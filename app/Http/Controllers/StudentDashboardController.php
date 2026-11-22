<?php

namespace App\Http\Controllers;

use App\Jobs\GenerateSkpiCredentialJob;
use App\Models\Activity;
use App\Models\SkpiCategory;
use App\Services\AiCertificateParserService;
use App\Services\AuditService;
use App\Services\CertificateVaultService;
use App\Services\CredentialCryptoService;
use App\Services\SemanticTalentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\View\View;

class StudentDashboardController extends Controller
{
    public const GRADUATION_POINT_THRESHOLD = 100;

    public function index(Request $request): View
    {
        $student = $request->user()->load('studentProfile.advisor');
        $profile = $student->studentProfile;

        if (! $profile) {
            $defaultAdvisor = \App\Models\User::role('dosen')->first();
            $profile = \App\Models\StudentProfile::create([
                'user_id' => $student->id,
                'nim' => 'TMP-'.strtoupper(Str::random(8)),
                'prodi_name' => 'Sistem Informasi',
                'faculty_name' => 'Fakultas Ilmu Komputer',
                'semester' => 1,
                'advisor_id' => $defaultAdvisor?->id,
                'public_slug' => (Str::slug($student->name) ?: 'mahasiswa').'-'.strtolower(Str::random(4)),
                'is_public_enabled' => false,
                'skills_json' => [],
                'yudisium_status' => 'active',
            ])->load('advisor');
        }

        $activities = Activity::where('student_id', $student->id)
            ->with(['category', 'file', 'validations.reviewer', 'credential'])
            ->latest()
            ->get();

        $categories = SkpiCategory::orderByDesc('is_mandatory')->orderBy('competency_domain')->get();
        $approvedActivities = $activities->where('status', 'approved');
        $totalPoints = (int) $approvedActivities->sum('awarded_points');
        $progressPercent = min(100, (int) round(($totalPoints / self::GRADUATION_POINT_THRESHOLD) * 100));

        // Mandatory category completion tracker
        $mandatoryCategories = $categories->where('is_mandatory', true)->map(function (SkpiCategory $cat) use ($approvedActivities) {
            return [
                'category' => $cat,
                'fulfilled' => $approvedActivities->where('category_id', $cat->id)->isNotEmpty(),
            ];
        });
        $allMandatoryMet = $mandatoryCategories->every(fn ($row) => $row['fulfilled']);
        $canRequestSkpi = $totalPoints >= self::GRADUATION_POINT_THRESHOLD && $allMandatoryMet;

        $skillRadar = $profile ? $profile->skillRadar() : [];
        $credentials = $student->issuedCredentials()->latest('issued_at')->get();
        $notifications = $student->hubNotifications()->take(6)->get();

        return view('student.dashboard', compact(
            'student',
            'profile',
            'activities',
            'categories',
            'totalPoints',
            'progressPercent',
            'mandatoryCategories',
            'allMandatoryMet',
            'canRequestSkpi',
            'skillRadar',
            'credentials',
            'notifications'
        ));
    }

    /**
     * AJAX endpoint for the Progressive Wizard Step 1: AI Smart Certificate Parser.
     */
    public function aiParse(Request $request, AiCertificateParserService $parser): JsonResponse
    {
        $request->validate([
            'certificate_file' => ['required', 'file', 'max:2048'],
            'hint_text' => ['nullable', 'string', 'max:400'],
        ]);

        $result = $parser->parseUploadedCertificate(
            $request->file('certificate_file'),
            $request->input('hint_text')
        );

        return response()->json([
            'ok' => true,
            'parsed' => $result,
        ]);
    }

    /**
     * Progressive Wizard final submission: Zero-Trust Vault + SHA-256 anti-duplication + Activity creation.
     */
    public function storeActivity(
        Request $request,
        CertificateVaultService $vault,
        AuditService $audit
    ): RedirectResponse {
        $student = $request->user();

        $data = $request->validate([
            'category_id' => ['required', 'exists:skpi_categories,id'],
            'title_id' => ['required', 'string', 'max:180'],
            'title_en' => ['required', 'string', 'max:180'],
            'organizer' => ['required', 'string', 'max:160'],
            'role_in_activity' => ['required', 'string', 'max:80'],
            'activity_date' => ['required', 'date', 'before_or_equal:today'],
            'external_verify_url' => ['nullable', 'url', 'max:512'],
            'is_public' => ['nullable', 'boolean'],
            'submit_mode' => ['required', 'in:draft,submitted'],
            'ai_confidence_score' => ['nullable', 'integer', 'min:0', 'max:100'],
            'certificate_file' => ['required', 'file', 'max:2048'],
        ]);

        $category = SkpiCategory::findOrFail($data['category_id']);
        $trustTier = ! empty($data['external_verify_url'])
            ? Activity::TIER_VERIFIED_EXTERNAL
            : Activity::TIER_ADMIN_REVIEWED;

        $activity = DB::transaction(function () use ($student, $data, $category, $trustTier, $request, $vault) {
            $activity = Activity::create([
                'student_id' => $student->id,
                'category_id' => $category->id,
                'title_id' => $data['title_id'],
                'title_en' => $data['title_en'],
                'organizer' => $data['organizer'],
                'role_in_activity' => $data['role_in_activity'],
                'activity_date' => $data['activity_date'],
                'status' => $data['submit_mode'],
                'trust_tier' => $trustTier,
                'awarded_points' => $category->default_points,
                'external_verify_url' => $data['external_verify_url'] ?? null,
                'is_public' => (bool) ($data['is_public'] ?? true),
                'ai_confidence_score' => $data['ai_confidence_score'] ?? 86,
            ]);

            $vault->inspectAndStore($request->file('certificate_file'), $student, $activity);

            return $activity;
        });

        $audit->log('activity.submitted', $activity, [
            'status' => $activity->status,
            'trust_tier' => $activity->trust_tier,
        ], $student);

        $msg = $activity->status === 'submitted'
            ? 'Sertifikat berhasil diamankan di Private Vault (SHA-256 terverifikasi) dan dikirim ke antrean Dosen PA.'
            : 'Draf aktivitas berhasil disimpan.';

        return redirect()->route('mahasiswa.dashboard')->with('success', $msg);
    }

    public function submitDraft(Request $request, Activity $activity, AuditService $audit): RedirectResponse
    {
        $this->authorize('update', $activity);

        $activity->update(['status' => 'submitted']);
        $audit->log('activity.draft_submitted', $activity, ['status' => 'submitted'], $request->user());

        return back()->with('success', 'Pengajuan dikirim ke Dosen PA untuk validasi.');
    }

    public function toggleVisibility(Request $request, Activity $activity): RedirectResponse
    {
        $this->authorize('toggleVisibility', $activity);

        $activity->update(['is_public' => ! $activity->is_public]);

        return back()->with('success', 'Visibilitas item portofolio diperbarui.');
    }

    public function updatePrivacy(Request $request, SemanticTalentService $semantic, AuditService $audit): RedirectResponse
    {
        $student = $request->user();
        $profile = $student->studentProfile;

        $data = $request->validate([
            'is_public_enabled' => ['nullable', 'boolean'],
            'public_slug' => ['required', 'alpha_dash', 'min:3', 'max:50', 'unique:student_profiles,public_slug,'.$profile->id],
            'headline' => ['nullable', 'string', 'max:160'],
            'bio' => ['nullable', 'string', 'max:600'],
            'skills_csv' => ['nullable', 'string', 'max:400'],
            'phone' => ['nullable', 'string', 'max:32'],
        ]);

        $skills = collect(explode(',', (string) ($data['skills_csv'] ?? '')))
            ->map(fn ($s) => trim($s))
            ->filter()
            ->unique()
            ->take(12)
            ->values()
            ->all();

        $profile->update([
            'is_public_enabled' => $request->boolean('is_public_enabled'),
            'public_slug' => Str::lower($data['public_slug']),
            'headline' => $data['headline'],
            'bio' => $data['bio'],
            'skills_json' => $skills,
            'phone' => $data['phone'] ?: $profile->phone,
        ]);

        $semantic->refreshStudentEmbedding($profile);

        $audit->log('profile.privacy_updated', $profile, [
            'is_public_enabled' => $profile->is_public_enabled,
            'public_slug' => $profile->public_slug,
        ], $student);

        return back()->with('success', 'Pengaturan privasi (UU PDP) & profil publik berhasil diperbarui.');
    }

    /**
     * Trigger Bilingual SKPI generation via Queue Job (sync-dispatched in local demo so document is immediately ready + queued semantics preserved).
     */
    public function requestSkpi(Request $request): RedirectResponse
    {
        $student = $request->user();
        $approvedActivities = $student->activities()->where('status', 'approved')->get();
        $totalPoints = (int) $approvedActivities->sum('awarded_points');

        if ($totalPoints < self::GRADUATION_POINT_THRESHOLD) {
            return back()->with('error', 'Total poin SKPI tervalidasi Anda belum mencapai ambang minimal '.self::GRADUATION_POINT_THRESHOLD.' poin.');
        }

        $mandatoryIds = SkpiCategory::where('is_mandatory', true)->pluck('id');
        $fulfilledIds = $approvedActivities->pluck('category_id')->unique();
        if ($mandatoryIds->diff($fulfilledIds)->isNotEmpty()) {
            return back()->with('error', 'Seluruh kategori wajib SKPI harus terpenuhi terlebih dahulu sebelum mengajukan penerbitan SKPI.');
        }

        // Dispatch through Laravel Queue pipeline (sync run for instant demo responsiveness, queueable in production)
        GenerateSkpiCredentialJob::dispatchSync($student->id);

        return back()->with('success', 'SKPI Dwibahasa berhasil diproses, ditandatangani secara kriptografis (Ed25519+HMAC), dan siap diunduh!');
    }

    /**
     * 1-Click ATS-Friendly CV Generator with embedded QR verification seal.
     */
    public function atsCv(Request $request, CredentialCryptoService $crypto): View
    {
        $student = $request->user()->load('studentProfile');
        $profile = $student->studentProfile;

        $activities = Activity::where('student_id', $student->id)
            ->where('status', 'approved')
            ->with(['category', 'credential'])
            ->orderByDesc('activity_date')
            ->get();

        $latestSkpi = $student->issuedCredentials()
            ->where('type', 'skpi')
            ->whereNull('revoked_at')
            ->latest('issued_at')
            ->first();

        $verifyUrl = $latestSkpi
            ? route('verify.show', $latestSkpi->registration_number)
            : ($profile->is_public_enabled ? route('portfolio.show', $profile->public_slug) : route('verify.index'));

        $qrSvg = $crypto->renderQrSvg($verifyUrl, 4);

        return view('student.ats-cv', compact('student', 'profile', 'activities', 'latestSkpi', 'verifyUrl', 'qrSvg'));
    }

    public function markNotificationsRead(Request $request): RedirectResponse
    {
        $request->user()->hubNotifications()->whereNull('read_at')->update(['read_at' => now()]);

        return back();
    }
}
