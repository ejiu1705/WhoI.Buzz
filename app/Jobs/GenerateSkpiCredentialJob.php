<?php

namespace App\Jobs;

use App\Models\IssuedCredential;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CredentialCryptoService;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Str;

class GenerateSkpiCredentialJob implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $studentId,
    ) {
        $this->onQueue('pdf-credentials');
    }

    public function handle(CredentialCryptoService $crypto, AuditService $audit): void
    {
        $student = User::with([
            'studentProfile',
            'activities' => fn ($q) => $q->where('status', 'approved')->with('category')->orderBy('activity_date'),
        ])->find($this->studentId);

        if (! $student || ! $student->studentProfile) {
            return;
        }

        $profile = $student->studentProfile;
        $approvedActivities = $student->activities;
        $totalPoints = $approvedActivities->sum('awarded_points');

        $regNumber = 'WB-SKPI-'.now()->format('Y').'-'.strtoupper(Str::random(6));
        $payload = [
            'registration_number' => $regNumber,
            'document_type' => 'SKPI_BILINGUAL_OFFICIAL',
            'student_name' => $student->name,
            'nim' => $profile->nim,
            'prodi' => $profile->prodi_name,
            'faculty' => $profile->faculty_name,
            'total_points' => $totalPoints,
            'approved_count' => $approvedActivities->count(),
            'tier_1_count' => $approvedActivities->where('trust_tier', 1)->count(),
            'tier_2_count' => $approvedActivities->where('trust_tier', 2)->count(),
            'tier_3_count' => $approvedActivities->where('trust_tier', 3)->count(),
            'activities_digest' => hash('sha256', $approvedActivities->pluck('id')->implode('|')),
            'issued_at' => now()->toIso8601String(),
        ];

        $signed = $crypto->signPayload($payload);

        $credential = IssuedCredential::create([
            'student_id' => $student->id,
            'activity_id' => null,
            'type' => 'skpi',
            'title' => 'Surat Keterangan Pendamping Ijazah (SKPI) Dwibahasa — '.$student->name,
            'registration_number' => $regNumber,
            'payload_json' => $payload,
            'signature_hash' => $signed['signature'],
            'signature_algo' => $signed['algorithm'],
            'issued_at' => now(),
        ]);

        $profile->forceFill(['yudisium_status' => 'eligible'])->save();

        $audit->log('skpi.issued', $credential, [
            'registration_number' => $regNumber,
            'total_points' => $totalPoints,
        ], $student);

        $audit->notify(
            $student,
            'skpi_ready',
            'SKPI Dwibahasa Siap Diunduh!',
            "Dokumen SKPI resmi ({$regNumber}) telah ditandatangani secara kriptografis ({$signed['algorithm']}) dan siap diverifikasi HRD.",
            route('credentials.document', $credential->registration_number)
        );
    }
}
