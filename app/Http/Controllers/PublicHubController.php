<?php

namespace App\Http\Controllers;

use App\Models\ActivityFile;
use App\Models\IssuedCredential;
use App\Models\StudentProfile;
use App\Services\CredentialCryptoService;
use App\Services\SemanticTalentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;
use Symfony\Component\HttpFoundation\StreamedResponse;

class PublicHubController extends Controller
{
    /**
     * Public Verification Landing & Lookup.
     */
    public function verifyIndex(Request $request): View
    {
        $query = trim((string) $request->query('code', ''));
        if ($query !== '') {
            $found = IssuedCredential::where('registration_number', $query)->first();
            if ($found) {
                return $this->verifyShow($found->registration_number, app(CredentialCryptoService::class));
            }
        }

        $recentCredentials = IssuedCredential::with('student.studentProfile')
            ->latest('issued_at')
            ->take(6)
            ->get();

        return view('public.verify', [
            'credential' => null,
            'verification' => null,
            'qrSvg' => null,
            'searchedCode' => $query,
            'notFound' => $query !== '',
            'recentCredentials' => $recentCredentials,
        ]);
    }

    /**
     * Live Cryptographic QR Verification Portal (Ed25519 + HMAC-SHA256 + Real-Time Revocation Check).
     */
    public function verifyShow(string $registrationNumber, CredentialCryptoService $crypto): View
    {
        $credential = IssuedCredential::with(['student.studentProfile', 'activity.category'])
            ->where('registration_number', $registrationNumber)
            ->first();

        $verification = $credential ? $crypto->verifyCredential($credential) : null;
        $qrSvg = $credential ? $crypto->renderQrSvg(route('verify.show', $credential->registration_number), 5) : null;

        $recentCredentials = IssuedCredential::with('student.studentProfile')
            ->latest('issued_at')
            ->take(6)
            ->get();

        return view('public.verify', [
            'credential' => $credential,
            'verification' => $verification,
            'qrSvg' => $qrSvg,
            'searchedCode' => $registrationNumber,
            'notFound' => $credential === null,
            'recentCredentials' => $recentCredentials,
        ]);
    }

    /**
     * Official Printable Bilingual SKPI / Credential Document with Cryptographic QR Seal.
     */
    public function credentialDocument(string $registrationNumber, CredentialCryptoService $crypto): View
    {
        $credential = IssuedCredential::with([
            'student.studentProfile',
            'student.activities' => fn ($q) => $q->where('status', 'approved')->with('category')->orderBy('activity_date'),
            'activity.category',
        ])->where('registration_number', $registrationNumber)->firstOrFail();

        $verification = $crypto->verifyCredential($credential);
        $qrSvg = $crypto->renderQrSvg(route('verify.show', $credential->registration_number), 5);

        return view('public.skpi-document', compact('credential', 'verification', 'qrSvg'));
    }

    /**
     * Interactive Public E-Portfolio (/@slug) respecting UU PDP Privacy-by-Default.
     */
    public function portfolio(string $slug, CredentialCryptoService $crypto): View
    {
        $profile = StudentProfile::with([
            'user.activities' => fn ($q) => $q->where('status', 'approved')
                ->where('is_public', true)
                ->with(['category', 'credential'])
                ->orderByDesc('activity_date'),
            'user.issuedCredentials' => fn ($q) => $q->whereNull('revoked_at')->latest('issued_at'),
        ])->where('public_slug', $slug)->firstOrFail();

        if (! $profile->is_public_enabled) {
            $viewer = request()->user();
            $canBypass = $viewer && ($viewer->id === $profile->user_id || $viewer->isAdmin());
            if (! $canBypass) {
                return view('public.portfolio-private', compact('profile'));
            }
        }

        $skillRadar = $profile->skillRadar();
        $approvedPoints = $profile->approvedPoints();
        $qrSvg = $crypto->renderQrSvg(route('portfolio.show', $profile->public_slug), 4);

        return view('public.portfolio', compact('profile', 'skillRadar', 'approvedPoints', 'qrSvg'));
    }

    /**
     * Semantic Talent Matching Hub (pgvector-compatible vector similarity search).
     */
    public function talentPool(Request $request, SemanticTalentService $semantic): View
    {
        $query = (string) $request->query('q', '');
        $prodi = $request->query('prodi') ?: null;
        $minSemester = max(1, (int) $request->query('min_semester', 1));

        $results = $semantic->search($query, $prodi, $minSemester);
        $prodiList = StudentProfile::where('is_public_enabled', true)->distinct()->pluck('prodi_name')->sort()->values();

        return view('public.talent-pool', compact('query', 'prodi', 'minSemester', 'results', 'prodiList'));
    }

    /**
     * Zero-Trust Private Vault Preview (requires valid Temporary Signed URL + authenticated RBAC policy).
     */
    public function vaultPreview(Request $request, ActivityFile $activityFile, \App\Services\CertificateVaultService $vault): StreamedResponse|\Illuminate\Http\Response
    {
        $activityFile->loadMissing('activity.student.studentProfile');
        $this->authorize('view', $activityFile->activity);

        if (! Storage::disk($activityFile->disk)->exists($activityFile->storage_path)) {
            if ($activityFile->mime_type === 'image/png' && function_exists('imagecreatetruecolor') && $activityFile->activity?->student) {
                $act = $activityFile->activity;
                $bytes = $vault->renderCertificateImageBytes(
                    $act->student,
                    $act,
                    $act->title_id,
                    $act->organizer,
                    $act->activity_date?->format('Y-m-d') ?? now()->format('Y-m-d'),
                    $act->trustTierMeta()['label'] ?? 'Verified Credential'
                );

                return response($bytes, 200, [
                    'Content-Type' => 'image/png',
                    'Cache-Control' => 'private, no-store, max-age=0',
                    'X-Robots-Tag' => 'noindex, nofollow',
                ]);
            }

            abort(404, 'Berkas tidak ditemukan di Private Vault.');
        }

        return Storage::disk($activityFile->disk)->response(
            $activityFile->storage_path,
            $activityFile->original_name,
            [
                'Content-Type' => $activityFile->mime_type,
                'Cache-Control' => 'private, no-store, max-age=0',
                'X-Robots-Tag' => 'noindex, nofollow',
            ]
        );
    }

    /**
     * SIAKAD Integration via JSON:API Resources (application/vnd.api+json).
     */
    public function siakadJsonApi(): JsonResponse
    {
        $students = StudentProfile::with([
            'user',
            'advisor',
            'user.activities' => fn ($q) => $q->where('status', 'approved'),
        ])->get();

        $data = $students->map(function (StudentProfile $p) {
            $points = (int) $p->user->activities->sum('awarded_points');

            return [
                'type' => 'student-skpi-records',
                'id' => $p->id,
                'attributes' => [
                    'nim' => $p->nim,
                    'full_name' => $p->user->name,
                    'prodi' => $p->prodi_name,
                    'faculty' => $p->faculty_name,
                    'semester' => $p->semester,
                    'approved_skpi_points' => $points,
                    'skpi_graduation_ready' => $points >= 100,
                    'yudisium_status' => $p->yudisium_status,
                    'privacy_public_opt_in' => $p->is_public_enabled,
                ],
                'relationships' => [
                    'advisor' => [
                        'data' => $p->advisor ? [
                            'type' => 'advisors',
                            'id' => $p->advisor->id,
                            'name' => $p->advisor->name,
                        ] : null,
                    ],
                ],
                'links' => [
                    'self' => url("/api/v1/siakad/students?nim={$p->nim}"),
                    'portfolio' => $p->is_public_enabled ? route('portfolio.show', $p->public_slug) : null,
                ],
            ];
        });

        return response()->json([
            'jsonapi' => ['version' => '1.1', 'profile' => 'WhoI.Buzz SIAKAD Sync'],
            'meta' => [
                'generated_at' => now()->toIso8601String(),
                'total_students' => $data->count(),
            ],
            'data' => $data,
        ], 200, ['Content-Type' => 'application/vnd.api+json']);
    }
}
