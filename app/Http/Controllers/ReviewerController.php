<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityValidation;
use App\Models\IssuedCredential;
use App\Services\AuditService;
use App\Services\CredentialCryptoService;
use App\Services\SemanticTalentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ReviewerController extends Controller
{
    public function index(Request $request): View
    {
        $reviewer = $request->user();

        $queueQuery = Activity::query()
            ->with(['student.studentProfile', 'category', 'file', 'validations.reviewer'])
            ->where('status', 'submitted');

        if ($reviewer->hasRole('dosen') && ! $reviewer->hasRole('admin')) {
            $queueQuery->whereHas('student.studentProfile', fn ($q) => $q->where('advisor_id', $reviewer->id));
        }

        $pendingQueue = $queueQuery->orderBy('created_at')->get();

        $selectedId = $request->query('selected');
        $selectedActivity = $selectedId
            ? $pendingQueue->firstWhere('id', $selectedId)
            : $pendingQueue->first();

        $signedPreviewUrl = $selectedActivity?->file?->temporarySignedPreviewUrl(5);

        $recentDecisions = ActivityValidation::with(['activity.student.studentProfile', 'reviewer'])
            ->when($reviewer->hasRole('dosen') && ! $reviewer->hasRole('admin'), fn ($q) => $q->where('reviewer_id', $reviewer->id))
            ->latest('reviewed_at')
            ->take(8)
            ->get();

        return view('reviewer.index', compact('reviewer', 'pendingQueue', 'selectedActivity', 'signedPreviewUrl', 'recentDecisions'));
    }

    public function decide(
        Request $request,
        Activity $activity,
        CredentialCryptoService $crypto,
        SemanticTalentService $semantic,
        AuditService $audit
    ): RedirectResponse {
        $this->authorize('review', $activity);

        $data = $request->validate([
            'decision' => ['required', 'in:approved,revision'],
            'trust_tier' => ['nullable', 'integer', 'in:1,2,3'],
            'awarded_points' => ['nullable', 'integer', 'min:1', 'max:100'],
            'notes' => ['nullable', 'string', 'max:500'],
        ]);

        if ($data['decision'] === 'revision' && empty(trim((string) ($data['notes'] ?? '')))) {
            return back()->with('error', 'Catatan revisi wajib diisi agar mahasiswa mengetahui perbaikan yang diperlukan.');
        }

        $reviewer = $request->user();

        if ($data['decision'] === 'approved') {
            $this->approveSingleActivity(
                $activity,
                $reviewer->id,
                (int) ($data['trust_tier'] ?? $activity->trust_tier),
                (int) ($data['awarded_points'] ?? $activity->awarded_points),
                $data['notes'] ?: 'Diverifikasi dan disetujui oleh Dosen PA.',
                $crypto,
                $semantic,
                $audit
            );

            return redirect()->route('dosen.dashboard')
                ->with('success', "Aktivitas \"{$activity->title_id}\" disetujui, dikunci permanen (Immutable), dan ditandatangani secara kriptografis!");
        }

        DB::transaction(function () use ($activity, $reviewer, $data, $audit) {
            $activity->loadMissing('student');
            $activity->update(['status' => 'revision']);

            ActivityValidation::create([
                'activity_id' => $activity->id,
                'reviewer_id' => $reviewer->id,
                'decision' => 'revision',
                'notes' => $data['notes'],
                'reviewed_at' => now(),
            ]);

            $audit->log('activity.revision_requested', $activity, ['notes' => $data['notes']], $reviewer);
            $audit->notify(
                $activity->student,
                'activity_revision',
                'Perlu Revisi Berkas Sertifikat',
                "Dosen PA meminta revisi untuk \"{$activity->title_id}\": {$data['notes']}",
                route('mahasiswa.dashboard')
            );
        });

        return redirect()->route('dosen.dashboard')
            ->with('success', 'Permintaan revisi terkirim ke mahasiswa beserta catatan perbaikan.');
    }

    /**
     * Bulk Approval for high-throughput advisor reviews (3x faster workflow).
     */
    public function bulkApprove(
        Request $request,
        CredentialCryptoService $crypto,
        SemanticTalentService $semantic,
        AuditService $audit
    ): RedirectResponse {
        $data = $request->validate([
            'activity_ids' => ['required', 'array', 'min:1'],
            'activity_ids.*' => ['string', 'exists:activities,id'],
        ]);

        $reviewer = $request->user();
        $activities = Activity::with('student.studentProfile')
            ->whereIn('id', $data['activity_ids'])
            ->where('status', 'submitted')
            ->get();

        $approvedCount = 0;
        foreach ($activities as $activity) {
            if ($reviewer->can('review', $activity)) {
                $this->approveSingleActivity(
                    $activity,
                    $reviewer->id,
                    $activity->trust_tier,
                    $activity->awarded_points,
                    'Disetujui massal (Bulk Approval) setelah pemeriksaan indikator AI.',
                    $crypto,
                    $semantic,
                    $audit
                );
                $approvedCount++;
            }
        }

        return redirect()->route('dosen.dashboard')
            ->with('success', "{$approvedCount} aktivitas berhasil disetujui massal, dikunci permanen, dan ditambahkan ke poin SKPI!");
    }

    private function approveSingleActivity(
        Activity $activity,
        string $reviewerId,
        int $trustTier,
        int $awardedPoints,
        string $notes,
        CredentialCryptoService $crypto,
        SemanticTalentService $semantic,
        AuditService $audit
    ): void {
        $activity->loadMissing('student.studentProfile');
        DB::transaction(function () use ($activity, $reviewerId, $trustTier, $awardedPoints, $notes, $crypto, $semantic, $audit) {
            $activity->update([
                'status' => 'approved',
                'trust_tier' => $trustTier,
                'awarded_points' => $awardedPoints,
                'locked_at' => now(), // Permanent Immutable Lock
            ]);

            ActivityValidation::create([
                'activity_id' => $activity->id,
                'reviewer_id' => $reviewerId,
                'decision' => 'approved',
                'notes' => $notes,
                'reviewed_at' => now(),
            ]);

            // Issue verifiable cryptographic credential for this approved activity
            $regNumber = 'WB-CERT-'.now()->format('Y').'-'.strtoupper(Str::random(6));
            $payload = [
                'registration_number' => $regNumber,
                'document_type' => 'VERIFIED_ACTIVITY_CREDENTIAL',
                'activity_id' => $activity->id,
                'student_name' => $activity->student->name,
                'nim' => $activity->student->studentProfile?->nim,
                'title_id' => $activity->title_id,
                'title_en' => $activity->title_en,
                'organizer' => $activity->organizer,
                'trust_tier' => $trustTier,
                'awarded_points' => $awardedPoints,
                'verified_at' => now()->toIso8601String(),
            ];
            $signed = $crypto->signPayload($payload);

            $credential = IssuedCredential::create([
                'student_id' => $activity->student_id,
                'activity_id' => $activity->id,
                'type' => 'certificate',
                'title' => $activity->title_id,
                'registration_number' => $regNumber,
                'payload_json' => $payload,
                'signature_hash' => $signed['signature'],
                'signature_algo' => $signed['algorithm'],
                'issued_at' => now(),
            ]);

            // Update vector embedding for Semantic Talent Pool
            if ($activity->student->studentProfile) {
                $semantic->refreshStudentEmbedding($activity->student->studentProfile);
            }

            $audit->log('activity.approved', $activity, [
                'trust_tier' => $trustTier,
                'awarded_points' => $awardedPoints,
                'credential_reg' => $regNumber,
            ]);

            $audit->notify(
                $activity->student,
                'activity_approved',
                'Capaian Disetujui & Terkunci (+'.$awardedPoints.' Poin SKPI)',
                "\"{$activity->title_id}\" resmi berstatus Tier {$trustTier} dengan kode verifikasi {$regNumber}. Siap ditambahkan ke LinkedIn!",
                route('verify.show', $credential->registration_number)
            );
        });
    }
}
