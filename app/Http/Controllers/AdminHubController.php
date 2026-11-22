<?php

namespace App\Http\Controllers;

use App\Models\Activity;
use App\Models\ActivityValidation;
use App\Models\AuditLog;
use App\Models\IssuedCredential;
use App\Models\SkpiCategory;
use App\Models\StudentProfile;
use App\Services\AuditService;
use App\Services\CertificateVaultService;
use App\Services\CredentialCryptoService;
use App\Services\SemanticTalentService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class AdminHubController extends Controller
{
    public function index(): View
    {
        $categories = SkpiCategory::withCount('activities')->orderByDesc('is_mandatory')->orderBy('code')->get();
        $credentials = IssuedCredential::with(['student.studentProfile', 'activity'])
            ->latest('issued_at')
            ->take(20)
            ->get();
        $auditLogs = AuditLog::with('actor')->latest('created_at')->take(25)->get();
        $students = StudentProfile::with('user')->orderBy('nim')->get();

        $summary = [
            'total_students' => $students->count(),
            'tier1_autoclaims' => Activity::where('trust_tier', 1)->where('status', 'approved')->count(),
            'active_credentials' => IssuedCredential::whereNull('revoked_at')->count(),
            'revoked_credentials' => IssuedCredential::whereNotNull('revoked_at')->count(),
        ];

        return view('admin.index', compact('categories', 'credentials', 'auditLogs', 'students', 'summary'));
    }

    /**
     * Auto-Claim Internal Campus Batch Issuer:
     * Imports a list of NIMs (CSV/newlines) -> automatically issues Tier 1 Gold Badge credentials
     * with Ed25519+HMAC signatures directly into each student's verified portfolio.
     */
    public function autoClaimBatch(
        Request $request,
        CredentialCryptoService $crypto,
        CertificateVaultService $vault,
        SemanticTalentService $semantic,
        AuditService $audit
    ): RedirectResponse {
        $data = $request->validate([
            'category_id' => ['required', 'exists:skpi_categories,id'],
            'title_id' => ['required', 'string', 'max:180'],
            'title_en' => ['required', 'string', 'max:180'],
            'organizer' => ['required', 'string', 'max:160'],
            'role_in_activity' => ['required', 'string', 'max:80'],
            'activity_date' => ['required', 'date'],
            'nims_list' => ['required', 'string', 'max:5000'],
        ]);

        $nims = collect(preg_split('/[\s,;]+/', $data['nims_list']) ?: [])
            ->map(fn ($n) => trim($n))
            ->filter()
            ->unique()
            ->values();

        $profiles = StudentProfile::with('user')->whereIn('nim', $nims)->get();
        if ($profiles->isEmpty()) {
            return back()->with('error', 'Tidak ada NIM yang cocok ditemukan di database kampus.');
        }

        $category = SkpiCategory::findOrFail($data['category_id']);
        $admin = $request->user();
        $issuedCount = 0;

        DB::transaction(function () use ($profiles, $category, $data, $admin, $crypto, $vault, $semantic, $audit, &$issuedCount) {
            foreach ($profiles as $profile) {
                $student = $profile->user;

                $activity = Activity::create([
                    'student_id' => $student->id,
                    'category_id' => $category->id,
                    'title_id' => $data['title_id'],
                    'title_en' => $data['title_en'],
                    'organizer' => $data['organizer'],
                    'role_in_activity' => $data['role_in_activity'],
                    'activity_date' => $data['activity_date'],
                    'status' => 'approved',
                    'trust_tier' => Activity::TIER_CAMPUS_ISSUED, // Tier 1 Gold Badge
                    'awarded_points' => $category->default_points,
                    'is_public' => true,
                    'ai_confidence_score' => 100,
                    'locked_at' => now(),
                ]);

                $vault->storeDemoCertificateImage(
                    $student,
                    $activity,
                    $data['title_id'],
                    $data['organizer'],
                    $data['activity_date'],
                    'Tier 1 — Campus Issued (Auto-Claim Gold Badge)'
                );

                ActivityValidation::create([
                    'activity_id' => $activity->id,
                    'reviewer_id' => $admin->id,
                    'decision' => 'approved',
                    'notes' => 'Auto-Claim diterbitkan langsung oleh Admin Kemahasiswaan (Tier 1 Gold Badge).',
                    'reviewed_at' => now(),
                ]);

                $regNumber = 'WB-T1-'.now()->format('Y').'-'.strtoupper(Str::random(6));
                $payload = [
                    'registration_number' => $regNumber,
                    'document_type' => 'CAMPUS_AUTOCLAIM_TIER1',
                    'activity_id' => $activity->id,
                    'student_name' => $student->name,
                    'nim' => $profile->nim,
                    'title_id' => $data['title_id'],
                    'title_en' => $data['title_en'],
                    'organizer' => $data['organizer'],
                    'trust_tier' => 1,
                    'awarded_points' => $category->default_points,
                    'issued_at' => now()->toIso8601String(),
                ];
                $signed = $crypto->signPayload($payload);

                IssuedCredential::create([
                    'student_id' => $student->id,
                    'activity_id' => $activity->id,
                    'type' => 'certificate',
                    'title' => $data['title_id'],
                    'registration_number' => $regNumber,
                    'payload_json' => $payload,
                    'signature_hash' => $signed['signature'],
                    'signature_algo' => $signed['algorithm'],
                    'issued_at' => now(),
                ]);

                $semantic->refreshStudentEmbedding($profile);

                $audit->notify(
                    $student,
                    'auto_claim',
                    'Sertifikat Auto-Claim Tier 1 Masuk Portofolio!',
                    "Kampus menerbitkan sertifikat resmi \"{$data['title_id']}\" (+{$category->default_points} Poin SKPI, Gold Badge {$regNumber}).",
                    route('verify.show', $regNumber)
                );

                $issuedCount++;
            }

            $audit->log('admin.autoclaim_batch', null, [
                'event' => $data['title_id'],
                'issued_count' => $issuedCount,
                'nims' => $profiles->pluck('nim')->all(),
            ], $admin);
        });

        return back()->with('success', "Auto-Claim berhasil! {$issuedCount} sertifikat Tier 1 (Gold Badge) langsung diterbitkan & dikunci ke portofolio mahasiswa.");
    }

    public function storeCategory(Request $request, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'code' => ['required', 'string', 'max:32', 'unique:skpi_categories,code'],
            'name_id' => ['required', 'string', 'max:160'],
            'name_en' => ['required', 'string', 'max:160'],
            'competency_domain' => ['required', 'in:Leadership,Technical,Communication,Social,Academic'],
            'default_points' => ['required', 'integer', 'min:5', 'max:60'],
            'level' => ['required', 'in:Lokal,Nasional,Internasional'],
            'is_mandatory' => ['nullable', 'boolean'],
        ]);

        $cat = new SkpiCategory([
            ...$data,
            'curriculum_version' => '2026-V1',
            'is_mandatory' => $request->boolean('is_mandatory'),
        ]);
        $cat->created_by = $request->user()->id;
        $cat->save();

        $audit->log('skpi_category.created', $cat, $data, $request->user());

        return back()->with('success', "Kategori kurikulum SKPI \"{$cat->name_id}\" berhasil ditambahkan.");
    }

    /**
     * Public Revocation Registry: immediately invalidates a credential & reflects on QR Verification page.
     */
    public function revokeCredential(Request $request, IssuedCredential $credential, AuditService $audit): RedirectResponse
    {
        $data = $request->validate([
            'revoke_reason' => ['required', 'string', 'min:8', 'max:400'],
        ]);

        DB::transaction(function () use ($credential, $data, $request, $audit) {
            $credential->update([
                'revoked_at' => now(),
                'revoke_reason' => $data['revoke_reason'],
            ]);

            if ($credential->activity) {
                $credential->activity->update(['status' => 'revoked']);
            }

            $audit->log('credential.revoked', $credential, [
                'registration_number' => $credential->registration_number,
                'reason' => $data['revoke_reason'],
            ], $request->user());
        });

        return back()->with('success', "Kredensial {$credential->registration_number} resmi DICABUT. Halaman verifikasi QR kini langsung menampilkan status TIDAK BERLAKU.");
    }
}
