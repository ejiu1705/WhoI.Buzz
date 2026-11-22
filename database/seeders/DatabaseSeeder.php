<?php

namespace Database\Seeders;

use App\Models\Activity;
use App\Models\ActivityValidation;
use App\Models\IssuedCredential;
use App\Models\SkpiCategory;
use App\Models\StudentProfile;
use App\Models\User;
use App\Services\AuditService;
use App\Services\CertificateVaultService;
use App\Services\CredentialCryptoService;
use App\Services\SemanticTalentService;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        // Modul 5: Seed Spatie Roles, Permissions, and Users first
        $this->call([
            RoleSeeder::class,
            UserSeeder::class,
        ]);

        $crypto = app(CredentialCryptoService::class);
        $vault = app(CertificateVaultService::class);
        $semantic = app(SemanticTalentService::class);
        $audit = app(AuditService::class);

        $admin = User::where('email', 'admin.kemahasiswaan@campus.ac.id')->firstOrFail();
        $advisor = User::where('email', 'dr.hendrawan@campus.ac.id')->firstOrFail();
        $advisorB = User::where('email', 'dosen2@campus.ac.id')->firstOrFail();
        $nadia = User::where('email', 'nadia.putri@campus.ac.id')->firstOrFail();
        $bima = User::where('email', 'bima.santoso@campus.ac.id')->firstOrFail();
        $raka = User::where('email', 'raka.mahendra@campus.ac.id')->firstOrFail();

        // Seed Student Profiles
        $nadiaProfile = StudentProfile::firstOrCreate(['user_id' => $nadia->id], [
            'nim' => '2201010018',
            'prodi_name' => 'Teknik Informatika',
            'faculty_name' => 'Fakultas Ilmu Komputer',
            'semester' => 8,
            'advisor_id' => $advisor->id,
            'public_slug' => 'nadia-putri',
            'is_public_enabled' => true,
            'phone' => '+62 812-3456-7890',
            'headline' => 'Cybersecurity Researcher • CTF National Champion • Security Engineer Intern',
            'bio' => 'Mahasiswa semester akhir Teknik Informatika dengan fokus keamanan siber, kriptografi terapan, penetration testing, dan arsitektur Zero-Trust.',
            'skills_json' => ['Cybersecurity', 'CTF', 'Penetration Testing', 'OWASP', 'Cryptography', 'Linux', 'Docker', 'Laravel Security'],
            'yudisium_status' => 'eligible',
        ]);

        $bimaProfile = StudentProfile::firstOrCreate(['user_id' => $bima->id], [
            'nim' => '2201010042',
            'prodi_name' => 'Sistem Informasi',
            'faculty_name' => 'Fakultas Ilmu Komputer',
            'semester' => 7,
            'advisor_id' => $advisor->id,
            'public_slug' => 'bima-santoso',
            'is_public_enabled' => true,
            'phone' => '+62 813-9876-5432',
            'headline' => 'Full-Stack Laravel 13 Engineer • Cloud DevOps & AI Systems Lead',
            'bio' => 'Membangun aplikasi web enterprise berbasis Laravel, PostgreSQL pgvector, dan container FrankenPHP. Mantan Ketua HMSI.',
            'skills_json' => ['Laravel 13', 'PostgreSQL', 'AWS Cloud', 'Docker', 'AI Integration', 'UI/UX Design', 'Public Speaking'],
            'yudisium_status' => 'active',
        ]);

        $rakaProfile = StudentProfile::firstOrCreate(['user_id' => $raka->id], [
            'nim' => '2302050091',
            'prodi_name' => 'Sains Data',
            'faculty_name' => 'Fakultas Ilmu Komputer',
            'semester' => 5,
            'advisor_id' => $advisorB->id,
            'public_slug' => 'raka-mahendra',
            'is_public_enabled' => false, // Privacy-by-Default UU PDP
            'phone' => '+62 857-2211-3344',
            'headline' => 'Data Science & Machine Learning Enthusiast',
            'bio' => 'Profil publik dinonaktifkan secara default sesuai prinsip UU PDP.',
            'skills_json' => ['Python', 'Machine Learning', 'SQL', 'Data Visualization'],
            'yudisium_status' => 'active',
        ]);

        // Seed SKPI Curriculum Categories with ownership (created_by alternating Dosen A & Dosen B per Modul 6 IDOR demo)
        $makeCat = function (array $data, User $owner): SkpiCategory {
            $cat = new SkpiCategory($data);
            $cat->created_by = $owner->id;
            $cat->save();

            return $cat;
        };

        $catTech = $makeCat([
            'curriculum_version' => '2026-V1',
            'code' => 'SKPI-TECH-01',
            'name_id' => 'Sertifikasi Profesi & Kompetensi Teknis',
            'name_en' => 'Professional & Technical Competency Certification',
            'competency_domain' => 'Technical',
            'is_mandatory' => true,
            'default_points' => 30,
            'level' => 'Internasional',
        ], $advisor);

        $catAcad = $makeCat([
            'curriculum_version' => '2026-V1',
            'code' => 'SKPI-ACAD-02',
            'name_id' => 'Prestasi Kompetisi Ilmiah / Nasional',
            'name_en' => 'National / International Academic Competition Award',
            'competency_domain' => 'Academic',
            'is_mandatory' => true,
            'default_points' => 35,
            'level' => 'Nasional',
        ], $advisorB);

        $catLead = $makeCat([
            'curriculum_version' => '2026-V1',
            'code' => 'SKPI-LEAD-03',
            'name_id' => 'Kepemimpinan & Organisasi Intrakampus',
            'name_en' => 'Campus Leadership & Organizational Governance',
            'competency_domain' => 'Leadership',
            'is_mandatory' => true,
            'default_points' => 25,
            'level' => 'Lokal',
        ], $advisor);

        $catComm = $makeCat([
            'curriculum_version' => '2026-V1',
            'code' => 'SKPI-COMM-04',
            'name_id' => 'Pembicara Seminar / Komunikasi Publik',
            'name_en' => 'Public Speaking & Academic Seminar Presentation',
            'competency_domain' => 'Communication',
            'is_mandatory' => false,
            'default_points' => 20,
            'level' => 'Nasional',
        ], $advisorB);

        $catSoc = $makeCat([
            'curriculum_version' => '2026-V1',
            'code' => 'SKPI-SOC-05',
            'name_id' => 'Pengabdian Masyarakat & Relawan SDGs',
            'name_en' => 'Community Service & SDGs Volunteering',
            'competency_domain' => 'Social',
            'is_mandatory' => false,
            'default_points' => 20,
            'level' => 'Lokal',
        ], $admin);

        // Helper for approved activity + demo certificate + cryptographic credential
        $seedApproved = function (
            User $student,
            StudentProfile $studentProfile,
            SkpiCategory $cat,
            array $attrs,
            string $regNumber,
            User $reviewer,
            bool $revoked = false
        ) use ($crypto, $vault) {
            $activity = Activity::create([
                'student_id' => $student->id,
                'category_id' => $cat->id,
                'title_id' => $attrs['title_id'],
                'title_en' => $attrs['title_en'],
                'organizer' => $attrs['organizer'],
                'role_in_activity' => $attrs['role_in_activity'],
                'activity_date' => $attrs['activity_date'],
                'status' => $revoked ? 'revoked' : 'approved',
                'trust_tier' => $attrs['trust_tier'],
                'awarded_points' => $attrs['awarded_points'],
                'external_verify_url' => $attrs['external_verify_url'] ?? null,
                'is_public' => true,
                'ai_confidence_score' => $attrs['ai_confidence_score'] ?? 96,
                'locked_at' => now()->subDays(5),
            ]);

            $vault->storeDemoCertificateImage(
                $student,
                $activity,
                $attrs['title_id'],
                $attrs['organizer'],
                $attrs['activity_date'],
                $activity->trustTierMeta()['label']
            );

            ActivityValidation::create([
                'activity_id' => $activity->id,
                'reviewer_id' => $reviewer->id,
                'decision' => $revoked ? 'revoked' : 'approved',
                'notes' => $revoked ? 'Dicabut karena ketidaksesuaian bukti kehadiran.' : 'Validasi selesai dan dikunci secara kriptografis.',
                'reviewed_at' => now()->subDays(5),
            ]);

            $payload = [
                'registration_number' => $regNumber,
                'document_type' => $attrs['trust_tier'] === 1 ? 'CAMPUS_AUTOCLAIM_TIER1' : 'VERIFIED_ACTIVITY_CREDENTIAL',
                'activity_id' => $activity->id,
                'student_name' => $student->name,
                'nim' => $studentProfile->nim,
                'title_id' => $attrs['title_id'],
                'title_en' => $attrs['title_en'],
                'organizer' => $attrs['organizer'],
                'trust_tier' => $attrs['trust_tier'],
                'awarded_points' => $attrs['awarded_points'],
                'verified_at' => now()->subDays(5)->toIso8601String(),
            ];
            $signed = $crypto->signPayload($payload);

            return IssuedCredential::create([
                'student_id' => $student->id,
                'activity_id' => $activity->id,
                'type' => 'certificate',
                'title' => $attrs['title_id'],
                'registration_number' => $regNumber,
                'payload_json' => $payload,
                'signature_hash' => $signed['signature'],
                'signature_algo' => $signed['algorithm'],
                'issued_at' => now()->subDays(5),
                'revoked_at' => $revoked ? now()->subDays(1) : null,
                'revoke_reason' => $revoked ? 'Ditemukan ketidaksesuaian administratif pada verifikasi ulang dokumen eksternal.' : null,
            ]);
        };

        // Nadia's Verified Portfolio (130 points -> SKPI Ready)
        $seedApproved($nadia, $nadiaProfile, $catAcad, [
            'title_id' => 'Juara 1 Capture The Flag (CTF) Keamanan Siber GEMASTIK XVII',
            'title_en' => '1st Place Winner in National Cybersecurity Capture The Flag (CTF) GEMASTIK XVII',
            'organizer' => 'Puspresnas Kemendikbudristek',
            'role_in_activity' => 'Juara 1 & Ketua Tim',
            'activity_date' => '2026-06-18',
            'trust_tier' => Activity::TIER_VERIFIED_EXTERNAL,
            'awarded_points' => 35,
            'external_verify_url' => 'https://puspresnas.kemdikbud.go.id/verify/gemastik-ctf-2026',
            'ai_confidence_score' => 98,
        ], 'WB-CERT-2026-CTF001', $advisor);

        $seedApproved($nadia, $nadiaProfile, $catTech, [
            'title_id' => 'Sertifikasi AWS Certified Security Specialty & Cloud Defense',
            'title_en' => 'AWS Certified Security Specialty & Cloud Defense Certification',
            'organizer' => 'Amazon Web Services (AWS)',
            'role_in_activity' => 'Certified Practitioner',
            'activity_date' => '2026-08-12',
            'trust_tier' => Activity::TIER_VERIFIED_EXTERNAL,
            'awarded_points' => 30,
            'external_verify_url' => 'https://www.credly.com/badges/whoibuzz-aws-sec-2026',
            'ai_confidence_score' => 97,
        ], 'WB-CERT-2026-AWS002', $advisor);

        $seedApproved($nadia, $nadiaProfile, $catLead, [
            'title_id' => 'Ketua Komunitas Keamanan Informasi & Cyber Defense Kampus',
            'title_en' => 'President of Campus Information Security & Cyber Defense Society',
            'organizer' => 'Direktorat Kemahasiswaan Kampus',
            'role_in_activity' => 'Ketua Umum',
            'activity_date' => '2026-01-15',
            'trust_tier' => Activity::TIER_CAMPUS_ISSUED,
            'awarded_points' => 25,
            'ai_confidence_score' => 100,
        ], 'WB-T1-2026-LEAD03', $admin);

        $seedApproved($nadia, $nadiaProfile, $catComm, [
            'title_id' => 'Pembicara Seminar Nasional Zero-Trust & Perlindungan Data UU PDP',
            'title_en' => 'Featured Speaker at National Seminar on Zero-Trust & Data Protection Law',
            'organizer' => 'Fakultas Ilmu Komputer',
            'role_in_activity' => 'Pembicara / Narasumber',
            'activity_date' => '2026-09-05',
            'trust_tier' => Activity::TIER_CAMPUS_ISSUED,
            'awarded_points' => 20,
            'ai_confidence_score' => 100,
        ], 'WB-T1-2026-COMM04', $admin);

        $seedApproved($nadia, $nadiaProfile, $catSoc, [
            'title_id' => 'Relawan Edukasi Literasi Keamanan Digital UMKM Daerah',
            'title_en' => 'Digital Security Literacy Volunteer for Regional SMEs',
            'organizer' => 'Forum Relawan TIK Nusantara',
            'role_in_activity' => 'Relawan Pengajar',
            'activity_date' => '2026-04-20',
            'trust_tier' => Activity::TIER_ADMIN_REVIEWED,
            'awarded_points' => 20,
            'ai_confidence_score' => 88,
        ], 'WB-CERT-2026-SOC005', $advisor);

        // Nadia's Official Bilingual SKPI
        $skpiReg = 'WB-SKPI-2026-NADIA1';
        $skpiPayload = [
            'registration_number' => $skpiReg,
            'document_type' => 'SKPI_BILINGUAL_OFFICIAL',
            'student_name' => $nadia->name,
            'nim' => $nadiaProfile->nim,
            'prodi' => $nadiaProfile->prodi_name,
            'faculty' => $nadiaProfile->faculty_name,
            'total_points' => 130,
            'approved_count' => 5,
            'tier_1_count' => 2,
            'tier_2_count' => 2,
            'tier_3_count' => 1,
            'issued_at' => now()->subDays(2)->toIso8601String(),
        ];
        $skpiSigned = $crypto->signPayload($skpiPayload);
        $skpiCred = IssuedCredential::create([
            'student_id' => $nadia->id,
            'activity_id' => null,
            'type' => 'skpi',
            'title' => 'Surat Keterangan Pendamping Ijazah (SKPI) Dwibahasa — '.$nadia->name,
            'registration_number' => $skpiReg,
            'payload_json' => $skpiPayload,
            'signature_hash' => $skpiSigned['signature'],
            'signature_algo' => $skpiSigned['algorithm'],
            'issued_at' => now()->subDays(2),
        ]);

        // Bima's Approved + Revoked Sample
        $seedApproved($bima, $bimaProfile, $catTech, [
            'title_id' => 'Sertifikasi Dicoding Expert Back-End & Arsitektur Laravel Modern',
            'title_en' => 'Dicoding Expert Back-End & Modern Laravel Architecture Certification',
            'organizer' => 'Dicoding Indonesia',
            'role_in_activity' => 'Graduated Expert',
            'activity_date' => '2026-07-10',
            'trust_tier' => Activity::TIER_VERIFIED_EXTERNAL,
            'awarded_points' => 30,
            'external_verify_url' => 'https://www.dicoding.com/certificates/verify-bima-2026',
            'ai_confidence_score' => 95,
        ], 'WB-CERT-2026-LAR010', $advisor);

        $seedApproved($bima, $bimaProfile, $catLead, [
            'title_id' => 'Ketua Himpunan Mahasiswa Sistem Informasi Periode 2025/2026',
            'title_en' => 'Chairman of Information Systems Student Association 2025/2026',
            'organizer' => 'BEM & Kemahasiswaan Kampus',
            'role_in_activity' => 'Ketua Himpunan',
            'activity_date' => '2026-02-01',
            'trust_tier' => Activity::TIER_CAMPUS_ISSUED,
            'awarded_points' => 25,
            'ai_confidence_score' => 100,
        ], 'WB-T1-2026-HMSI11', $admin);

        $seedApproved($bima, $bimaProfile, $catComm, [
            'title_id' => 'Workshop Eksternal Manajemen Proyek (Sampel Status Dicabut)',
            'title_en' => 'External Project Management Workshop (Revoked Status Sample)',
            'organizer' => 'Lembaga Pelatihan Tidak Terakreditasi',
            'role_in_activity' => 'Peserta',
            'activity_date' => '2026-03-11',
            'trust_tier' => Activity::TIER_ADMIN_REVIEWED,
            'awarded_points' => 15,
            'ai_confidence_score' => 62,
        ], 'WB-REVOKED-2026-999', $admin, true);

        // Pending Submissions for Dosen Split-Screen Reviewer
        $pending1 = Activity::create([
            'student_id' => $bima->id,
            'category_id' => $catAcad->id,
            'title_id' => 'Juara 2 Hackathon AI & Cloud Native Kampus Merdeka 2026',
            'title_en' => '2nd Place Winner at Kampus Merdeka AI & Cloud Native Hackathon 2026',
            'organizer' => 'Google Developer Groups & Kemendikbudristek',
            'role_in_activity' => 'Juara 2 / Full-Stack Lead',
            'activity_date' => '2026-10-18',
            'status' => 'submitted',
            'trust_tier' => Activity::TIER_VERIFIED_EXTERNAL,
            'awarded_points' => 35,
            'external_verify_url' => 'https://gdg.community.dev/verify/hackathon-ai-2026',
            'is_public' => true,
            'ai_confidence_score' => 94,
        ]);
        $vault->storeDemoCertificateImage($bima, $pending1, $pending1->title_id, $pending1->organizer, '2026-10-18', 'Menunggu Validasi Dosen PA — Tier 2');

        $pending2 = Activity::create([
            'student_id' => $nadia->id,
            'category_id' => $catTech->id,
            'title_id' => 'Pelatihan Red Team Adversary Simulation Lokal',
            'title_en' => 'Local Red Team Adversary Simulation Workshop',
            'organizer' => 'Komunitas Cyber Nusantara',
            'role_in_activity' => 'Peserta Aktif',
            'activity_date' => '2026-11-10',
            'status' => 'submitted',
            'trust_tier' => Activity::TIER_ADMIN_REVIEWED,
            'awarded_points' => 20,
            'external_verify_url' => null,
            'is_public' => true,
            'ai_confidence_score' => 81,
        ]);
        $vault->storeDemoCertificateImage($nadia, $pending2, $pending2->title_id, $pending2->organizer, '2026-11-10', 'Menunggu Validasi Dosen PA — Tier 3');

        $pending3 = Activity::create([
            'student_id' => $raka->id,
            'category_id' => $catTech->id,
            'title_id' => 'Sertifikasi BNSP Associate Data Scientist & Machine Learning',
            'title_en' => 'BNSP National Competency Certification: Associate Data Scientist',
            'organizer' => 'Badan Nasional Sertifikasi Profesi (BNSP)',
            'role_in_activity' => 'Kompeten / Certified',
            'activity_date' => '2026-11-02',
            'status' => 'submitted',
            'trust_tier' => Activity::TIER_VERIFIED_EXTERNAL,
            'awarded_points' => 30,
            'external_verify_url' => 'https://bnsp.go.id/verifikasi/ads-2026-raka',
            'is_public' => true,
            'ai_confidence_score' => 92,
        ]);
        $vault->storeDemoCertificateImage($raka, $pending3, $pending3->title_id, $pending3->organizer, '2026-11-02', 'Menunggu Validasi Dosen B — Tier 2 BNSP');

        // Refresh Semantic Embeddings
        foreach ([$nadiaProfile, $bimaProfile, $rakaProfile] as $prof) {
            $semantic->refreshStudentEmbedding($prof);
        }

        $audit->log('system.seeded', $skpiCred, [
            'curriculum' => '2026-V1',
            'rbac_engine' => 'spatie/laravel-permission',
            'api_auth' => 'laravel/sanctum',
        ], $admin);

        $audit->notify(
            $nadia,
            'skpi_ready',
            'SKPI Dwibahasa Resmi Terbit!',
            "Nomor Registrasi {$skpiReg} telah ditandatangani Ed25519+HMAC dan siap dibagikan ke HRD atau LinkedIn.",
            route('credentials.document', $skpiReg)
        );
    }
}
