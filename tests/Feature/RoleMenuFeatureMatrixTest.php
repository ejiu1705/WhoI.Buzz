<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\ActivityFile;
use App\Models\IssuedCredential;
use App\Models\SkpiCategory;
use App\Models\User;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RoleMenuFeatureMatrixTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(DatabaseSeeder::class);
    }

    private function makeUniquePng(int $seedColor = 10): string
    {
        $img = imagecreatetruecolor(90, 50);
        $bg = imagecolorallocate($img, ($seedColor * 17) % 255, ($seedColor * 31) % 255, ($seedColor * 47) % 255);
        imagefill($img, 0, 0, $bg);
        imagestring($img, 3, 8, 16, 'CERT-'.$seedColor, imagecolorallocate($img, 255, 255, 255));
        ob_start();
        imagepng($img);
        $bytes = (string) ob_get_clean();
        imagedestroy($img);

        return $bytes;
    }

    public function test_guest_menus_features_and_access_restrictions(): void
    {
        // 1. Beranda publik & redirect /beranda
        $home = $this->get('/')->assertOk();
        $home->assertSee('Masuk ke Akun')
            ->assertSee('Registrasi Akun Baru')
            ->assertDontSee('/api-client')
            ->assertDontSee('/admin/api-console');
        // Pada beranda awal (/), tombol Masuk/Daftar tidak muncul di navbar (hanya di konten halaman)
        $this->assertStringNotContainsString('<span>Masuk</span>', $home->getContent());
        $this->assertStringNotContainsString('<span>Daftar</span>', $home->getContent());

        $this->get('/beranda')->assertRedirect('/');

        // 2. Halaman publik: Verifikasi, Cetak Dokumen, Talent Pool, Portofolio Publik & Privat
        $this->get('/verify')->assertOk();
        $this->get('/verify?code=WB-SKPI-2026-NADIA1')->assertOk()->assertSee('TERVERIFIKASI SAH');
        $this->get('/verify?code=KODE-TIDAK-ADA')->assertOk();
        $this->get('/verify/WB-REVOKED-2026-999')->assertOk()->assertSee('DICABUT');
        $this->get('/credentials/WB-SKPI-2026-NADIA1/document')->assertOk()->assertSee('Nadia Putri Mahardika');

        $this->get('/talent-pool')->assertOk()->assertSee('Nadia Putri Mahardika');
        $this->get('/talent-pool?q=cyber+security&prodi=Teknik+Informatika&min_semester=5')->assertOk();
        $this->get('/@nadia-putri')->assertOk()->assertSee('Nadia Putri Mahardika');
        $this->get('/@raka-mahendra')->assertOk()->assertSee('Profil Publik Dinonaktifkan');

        // 3. Halaman Login & Registrasi (di halaman non-home, navbar menampilkan Masuk & Daftar)
        $loginPage = $this->get('/login')->assertOk()->assertSee('Masuk ke WhoI.Buzz');
        $this->assertStringContainsString('<span>Masuk</span>', $loginPage->getContent());
        $this->get('/register')->assertOk()->assertSee('Daftar Akun WhoI.Buzz');

        // Login gagal dengan sandi salah
        $this->post('/login', [
            'email' => 'nadia.putri@campus.ac.id',
            'password' => 'sandi-salah',
        ])->assertSessionHasErrors('email');

        // 4. Semua rute terautentikasi wajib redirect ke /login bagi Tamu
        $protectedRoutes = [
            '/dashboard',
            '/akun',
            '/logout/verify',
            '/kurikulum-skpi',
            '/laporan-progres',
            '/mahasiswa/dashboard',
            '/mahasiswa/ats-cv',
            '/dosen/dashboard',
            '/admin/dashboard',
            '/admin/users',
            '/admin/api-console',
            '/mitra/dashboard',
        ];

        foreach ($protectedRoutes as $route) {
            $this->get($route)->assertRedirect('/login');
        }

        // 5. Semua endpoint API wajib 401 bagi Tamu
        $this->getJson('/api/v1/me')->assertUnauthorized();
        $this->getJson('/api/v1/skpi-categories')->assertUnauthorized();
        $this->getJson('/api/v1/siakad/students')->assertUnauthorized();
    }

    public function test_mahasiswa_all_menus_features_and_strict_restrictions(): void
    {
        $nadia = User::where('email', 'nadia.putri@campus.ac.id')->firstOrFail();
        $bima = User::where('email', 'bima.santoso@campus.ac.id')->firstOrFail();
        $raka = User::where('email', 'raka.mahendra@campus.ac.id')->firstOrFail();
        $category = SkpiCategory::firstOrFail();

        // 1. Login -> redirect dashboard mahasiswa & verifikasi menu navbar
        $this->post('/login', [
            'email' => $nadia->email,
            'password' => 'password123',
        ])->assertRedirect('/mahasiswa/dashboard');

        $this->get('/dashboard')->assertRedirect('/mahasiswa/dashboard');

        $dash = $this->get('/mahasiswa/dashboard')->assertOk()->assertSee('Nadia Putri Mahardika');
        $dash->assertDontSee('/admin/dashboard')
            ->assertDontSee('/admin/users')
            ->assertDontSee('/dosen/dashboard')
            ->assertDontSee('/laporan-progres')
            ->assertDontSee('/admin/api-console');

        // 2. Menu yang boleh diakses Mahasiswa
        $this->get('/kurikulum-skpi')->assertOk();
        $this->get('/kurikulum-skpi?cari=SKPI&domain=Technical&hanya_wajib=1')->assertOk();
        $this->get("/kurikulum-skpi/{$category->id}")->assertOk();
        $this->get('/verify')->assertOk();
        $this->get('/mahasiswa/ats-cv')->assertOk()->assertSee('Nadia Putri Mahardika');

        // Info Akun (tanpa panel token API)
        $this->get('/akun')
            ->assertOk()
            ->assertSee('Pengaturan Profil Akun')
            ->assertDontSee('Token Akses Integrasi');

        // 3. Fitur AI Parse Sertifikat
        $parsePng = $this->makeUniquePng(101);
        $this->postJson('/mahasiswa/ai-parse', [
            'certificate_file' => UploadedFile::fake()->createWithContent('sertifikat_ai.png', $parsePng),
            'hint_text' => 'Juara 1 Lomba Keamanan Siber Nasional 2026',
        ])->assertOk()->assertJsonPath('ok', true)->assertJsonStructure(['ok', 'parsed']);

        // 4. Fitur Simpan Draft -> Submit ke Dosen PA
        $draftPng = $this->makeUniquePng(102);
        $this->post('/mahasiswa/activities', [
            'category_id' => $category->id,
            'title_id' => 'Kompetisi Keamanan Jaringan Kampus',
            'title_en' => 'Campus Network Security Competition',
            'organizer' => 'HMTI Nasional',
            'role_in_activity' => 'Finalis',
            'activity_date' => now()->subDays(5)->format('Y-m-d'),
            'submit_mode' => 'draft',
            'certificate_file' => UploadedFile::fake()->createWithContent('draft_cert.png', $draftPng),
        ])->assertRedirect('/mahasiswa/dashboard');

        $draftActivity = Activity::where('title_id', 'Kompetisi Keamanan Jaringan Kampus')->firstOrFail();
        $this->assertSame('draft', $draftActivity->status);

        $this->post("/mahasiswa/activities/{$draftActivity->id}/submit")->assertRedirect();
        $this->assertSame('submitted', $draftActivity->fresh()->status);

        // 5. Toggle visibilitas item portofolio & update privasi UU PDP
        $beforeVis = (bool) $draftActivity->fresh()->is_public;
        $this->post("/mahasiswa/activities/{$draftActivity->id}/visibility")->assertRedirect();
        $this->assertNotSame($beforeVis, (bool) $draftActivity->fresh()->is_public);

        $this->post('/mahasiswa/privacy', [
            'is_public_enabled' => '1',
            'public_slug' => 'nadia-putri',
            'headline' => 'Security & Cloud Engineer',
            'bio' => 'Mahasiswa aktif berprestasi.',
            'skills_csv' => 'Laravel, Cloud Security, CTF',
            'phone' => '081234567890',
        ])->assertRedirect();

        // 6. Tandai notifikasi dibaca
        $this->post('/mahasiswa/notifications/read')->assertRedirect();

        // 7. Pratinjau Vault Signed URL milik sendiri (200) vs milik mahasiswa lain (403)
        $ownFile = ActivityFile::where('activity_id', $draftActivity->id)->firstOrFail();
        $this->get($ownFile->temporarySignedPreviewUrl(5))->assertOk();
        $this->get("/vault/preview/{$ownFile->id}")->assertForbidden(); // tanpa signature

        // 8. Pengajuan SKPI Dwibahasa: Nadia memenuhi syarat (>=100 poin & wajib terpenuhi) -> sukses
        $this->post('/mahasiswa/skpi/request')
            ->assertRedirect()
            ->assertSessionHas('success');

        // Raka belum memenuhi syarat poin & wajib -> ditolak
        $this->actingAs($raka)
            ->post('/mahasiswa/skpi/request')
            ->assertRedirect()
            ->assertSessionHas('error');

        // 9. Update profil & ganti sandi di Info Akun Mandiri
        $this->actingAs($nadia)
            ->put('/akun', [
                'name' => 'Nadia Putri Mahardika',
                'email' => $nadia->email,
                'phone' => '081299990000',
            ])->assertRedirect()->assertSessionHas('success');

        $this->put('/akun/password', [
            'current_password' => 'password123',
            'password' => 'passwordBaru123',
            'password_confirmation' => 'passwordBaru123',
        ])->assertRedirect()->assertSessionHas('success');
        $this->assertTrue(Hash::check('passwordBaru123', $nadia->fresh()->password));

        // 10. Pembatasan Akses Role & Anti-IDOR Mahasiswa
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/api-console')->assertForbidden();
        $this->get('/dosen/dashboard')->assertForbidden();
        $this->get('/mitra/dashboard')->assertForbidden();
        $this->get('/laporan-progres')->assertForbidden();
        $this->get('/kurikulum-skpi/create')->assertForbidden();
        $this->post('/kurikulum-skpi', [])->assertForbidden();
        $this->get("/kurikulum-skpi/{$category->id}/edit")->assertForbidden();
        $this->delete("/kurikulum-skpi/{$category->id}")->assertForbidden();
        $this->post('/akun/tokens', ['token_name' => 'illegal'])->assertForbidden();

        // Anti-IDOR antar mahasiswa: Bima tidak boleh akses vault / submit / visibility milik Nadia
        $this->actingAs($bima)->get($ownFile->temporarySignedPreviewUrl(5))->assertForbidden();
        $this->actingAs($bima)->post("/mahasiswa/activities/{$draftActivity->id}/visibility")->assertForbidden();

        // 11. Verifikasi Logout -> redirect ke /
        $this->actingAs($nadia)->get('/logout/verify')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_dosen_all_menus_features_and_strict_restrictions(): void
    {
        $dosen1 = User::where('email', 'dr.hendrawan@campus.ac.id')->firstOrFail();
        $dosen2 = User::where('email', 'dosen2@campus.ac.id')->firstOrFail();
        $nadia = User::where('email', 'nadia.putri@campus.ac.id')->firstOrFail();
        $category = SkpiCategory::firstOrFail();

        // Siapkan 2 pengajuan baru dari Nadia (mahasiswa bimbingan Dr. Hendrawan)
        $this->actingAs($nadia)->post('/mahasiswa/activities', [
            'category_id' => $category->id,
            'title_id' => 'Workshop Audit Sistem Informasi',
            'title_en' => 'Information System Audit Workshop',
            'organizer' => 'ISACA Campus',
            'role_in_activity' => 'Peserta',
            'activity_date' => now()->subDays(3)->format('Y-m-d'),
            'submit_mode' => 'submitted',
            'certificate_file' => UploadedFile::fake()->createWithContent('audit1.png', $this->makeUniquePng(201)),
        ])->assertRedirect('/mahasiswa/dashboard');

        $this->actingAs($nadia)->post('/mahasiswa/activities', [
            'category_id' => $category->id,
            'title_id' => 'Pelatihan Cloud Architecture',
            'title_en' => 'Cloud Architecture Training',
            'organizer' => 'Cloud Lab',
            'role_in_activity' => 'Peserta',
            'activity_date' => now()->subDays(2)->format('Y-m-d'),
            'submit_mode' => 'submitted',
            'certificate_file' => UploadedFile::fake()->createWithContent('cloud2.png', $this->makeUniquePng(202)),
        ])->assertRedirect('/mahasiswa/dashboard');

        $actSingle = Activity::where('title_id', 'Workshop Audit Sistem Informasi')->firstOrFail();
        $actBulk = Activity::where('title_id', 'Pelatihan Cloud Architecture')->firstOrFail();
        $fileSingle = ActivityFile::where('activity_id', $actSingle->id)->firstOrFail();

        // 1. Login Dosen 1 -> redirect /dosen/dashboard & cek menu
        $this->actingAs($dosen1);
        $this->get('/dashboard')->assertRedirect('/dosen/dashboard');

        $dash = $this->get('/dosen/dashboard')->assertOk();
        $dash->assertSee('Workshop Audit Sistem Informasi')
            ->assertSee('/laporan-progres')
            ->assertDontSee('/admin/users')
            ->assertDontSee('/admin/api-console');

        $this->get('/laporan-progres')->assertOk()->assertSee('Nadia Putri Mahardika');
        $this->get('/akun')->assertOk()->assertDontSee('Token Akses Integrasi');

        // 2. Pratinjau Vault mahasiswa bimbingan -> 200 OK
        $this->get($fileSingle->temporarySignedPreviewUrl(5))->assertOk();

        // 3. Validasi Minta Revisi tanpa catatan -> error; dengan catatan -> status revision
        $this->post("/dosen/activities/{$actSingle->id}/decide", [
            'decision' => 'revision',
            'notes' => '',
        ])->assertRedirect()->assertSessionHas('error');

        $this->post("/dosen/activities/{$actSingle->id}/decide", [
            'decision' => 'revision',
            'notes' => 'Mohon unggah sertifikat dengan tanggal terbit yang jelas.',
        ])->assertRedirect('/dosen/dashboard')->assertSessionHas('success');
        $this->assertSame('revision', $actSingle->fresh()->status);

        // Mahasiswa kirim ulang -> Dosen Setujui Tunggal
        $this->actingAs($nadia)->post("/mahasiswa/activities/{$actSingle->id}/submit")->assertRedirect();
        $this->actingAs($dosen1)->post("/dosen/activities/{$actSingle->id}/decide", [
            'decision' => 'approved',
            'trust_tier' => 2,
            'awarded_points' => 25,
            'notes' => 'Berkas lengkap dan terverifikasi.',
        ])->assertRedirect('/dosen/dashboard')->assertSessionHas('success');

        $actSingle->refresh();
        $this->assertSame('approved', $actSingle->status);
        $this->assertNotNull($actSingle->locked_at);
        $this->assertDatabaseHas('issued_credentials', ['activity_id' => $actSingle->id]);

        // 4. Bulk Approve
        $this->actingAs($dosen1)->post('/dosen/bulk-approve', [
            'activity_ids' => [$actBulk->id],
        ])->assertRedirect('/dosen/dashboard')->assertSessionHas('success');
        $this->assertSame('approved', $actBulk->fresh()->status);

        // 5. CRUD Kurikulum SKPI milik Dosen 1 sendiri
        $this->get('/kurikulum-skpi/create')->assertOk();
        $this->post('/kurikulum-skpi', [
            'code' => 'SKPI-DSN-77',
            'name_id' => 'Asisten Laboratorium Semester',
            'name_en' => 'Semester Laboratory Assistant',
            'competency_domain' => 'Academic',
            'default_points' => 20,
            'level' => 'Lokal',
            'is_mandatory' => false,
        ])->assertRedirect('/kurikulum-skpi');

        $ownCat = SkpiCategory::where('code', 'SKPI-DSN-77')->firstOrFail();
        $this->get("/kurikulum-skpi/{$ownCat->id}/edit")->assertOk();
        $this->put("/kurikulum-skpi/{$ownCat->id}", [
            'code' => 'SKPI-DSN-77',
            'name_id' => 'Asisten Laboratorium Utama',
            'name_en' => 'Lead Laboratory Assistant',
            'competency_domain' => 'Academic',
            'default_points' => 25,
            'level' => 'Lokal',
            'is_mandatory' => false,
        ])->assertRedirect('/kurikulum-skpi');
        $this->assertSame(25, $ownCat->fresh()->default_points);

        // Anti-IDOR: Dosen 2 tidak boleh edit/hapus kategori Dosen 1, maupun review/intip vault bimbingan Dosen 1
        $this->actingAs($dosen2)->get("/kurikulum-skpi/{$ownCat->id}/edit")->assertForbidden();
        $this->actingAs($dosen2)->delete("/kurikulum-skpi/{$ownCat->id}")->assertForbidden();
        $this->actingAs($dosen2)->get($fileSingle->temporarySignedPreviewUrl(5))->assertForbidden();

        // Dosen 1 hapus kategori miliknya sendiri -> sukses
        $this->actingAs($dosen1)->delete("/kurikulum-skpi/{$ownCat->id}")->assertRedirect('/kurikulum-skpi');
        $this->assertDatabaseMissing('skpi_categories', ['id' => $ownCat->id]);

        // 6. Pembatasan Akses Role Dosen
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/api-console')->assertForbidden();
        $this->get('/mahasiswa/dashboard')->assertForbidden();
        $this->get('/mitra/dashboard')->assertForbidden();
        $this->post('/akun/tokens', ['token_name' => 'forbidden'])->assertForbidden();
        $this->postJson('/api/v1/login', [
            'email' => $dosen1->email,
            'password' => 'password123',
        ])->assertForbidden();

        // 7. Logout -> redirect ke /
        $this->get('/logout/verify')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_admin_all_menus_features_and_strict_restrictions(): void
    {
        $admin = User::where('email', 'admin.kemahasiswaan@campus.ac.id')->firstOrFail();
        $category = SkpiCategory::firstOrFail();

        $this->actingAs($admin);
        $this->get('/dashboard')->assertRedirect('/admin/dashboard');

        // 1. Cek semua menu Admin (dan tidak ada link API di navbar)
        $dash = $this->get('/admin/dashboard')->assertOk();
        $dash->assertSee('/admin/users')
            ->assertSee('/laporan-progres')
            ->assertSee('/kurikulum-skpi')
            ->assertDontSee('/api-client')
            ->assertDontSee('/admin/api-console');

        $this->get('/admin/users')->assertOk();
        $this->get('/admin/users?cari=nadia&role=mahasiswa')->assertOk()->assertSee('Nadia Putri Mahardika');
        $this->get('/admin/users/create')->assertOk();
        $this->get('/laporan-progres')->assertOk();
        $this->get('/dosen/dashboard')->assertOk();
        $this->get('/mitra/dashboard')->assertOk();
        $this->get('/admin/api-console')->assertOk();
        $this->get('/akun')->assertOk()->assertSee('Token Akses Integrasi');

        // 2. Fitur Auto-Claim Tier 1 Batch
        $this->post('/admin/auto-claim', [
            'category_id' => $category->id,
            'title_id' => 'Seminar Nasional Keamanan Kampus 2026',
            'title_en' => 'National Campus Security Seminar 2026',
            'organizer' => 'Direktorat Kemahasiswaan',
            'role_in_activity' => 'Peserta Terpilih',
            'activity_date' => now()->subDay()->format('Y-m-d'),
            'nims_list' => '2201010018, 2201010042',
        ])->assertRedirect()->assertSessionHas('success');

        $issuedBatch = IssuedCredential::where('title', 'Seminar Nasional Keamanan Kampus 2026')->get();
        $this->assertCount(2, $issuedBatch);

        // 3. Fitur Cabut Kredensial (Revocation) + refleksi langsung di halaman Verifikasi Publik
        $targetCred = $issuedBatch->first();
        $this->post("/admin/credentials/{$targetCred->id}/revoke", [
            'revoke_reason' => 'Ditemukan kekeliruan data kehadiran peserta pada daftar hadir resmi.',
        ])->assertRedirect()->assertSessionHas('success');

        $this->assertNotNull($targetCred->fresh()->revoked_at);
        $this->get("/verify/{$targetCred->registration_number}")
            ->assertOk()
            ->assertSee('DICABUT');

        // 4. Fitur Manajemen Pengguna (CRUD + proteksi hapus diri sendiri)
        $this->post('/admin/users', [
            'name' => 'Mitra Uji Lengkap',
            'email' => 'mitra.uji@company.id',
            'role' => 'mitra',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/admin/users');

        $newUser = User::where('email', 'mitra.uji@company.id')->firstOrFail();
        $this->get("/admin/users/{$newUser->id}")->assertOk()->assertSee('Mitra Uji Lengkap');
        $this->get("/admin/users/{$newUser->id}/edit")->assertOk();
        $this->put("/admin/users/{$newUser->id}", [
            'name' => 'Mitra Uji Diperbarui',
            'email' => 'mitra.uji@company.id',
            'role' => 'mitra',
        ])->assertRedirect('/admin/users');
        $this->assertSame('Mitra Uji Diperbarui', $newUser->fresh()->name);

        // Admin tidak boleh menghapus dirinya sendiri
        $this->delete("/admin/users/{$admin->id}")
            ->assertRedirect()
            ->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $admin->id]);

        // Admin hapus pengguna lain -> sukses
        $this->delete("/admin/users/{$newUser->id}")->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $newUser->id]);

        // 5. Fitur Buat & Cabut Token API di halaman Info Akun (khusus Admin)
        $this->post('/akun/tokens', ['token_name' => 'Postman QA'])
            ->assertRedirect()
            ->assertSessionHas('new_api_token');
        $tokenId = $admin->tokens()->latest()->firstOrFail()->id;
        $this->delete("/akun/tokens/{$tokenId}")->assertRedirect()->assertSessionHas('success');

        // Proteksi hapus satu-satunya akun Admin lewat Info Akun
        $this->delete('/akun', ['delete_password' => 'password123'])
            ->assertRedirect()
            ->assertSessionHas('error');

        // 6. Admin tidak boleh masuk dashboard khusus Mahasiswa
        $this->get('/mahasiswa/dashboard')->assertForbidden();

        // 7. Logout -> redirect ke /
        $this->get('/logout/verify')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }

    public function test_mitra_all_menus_features_and_strict_restrictions(): void
    {
        $mitra = User::where('email', 'hrd.mitra@techcorp.id')->firstOrFail();
        $category = SkpiCategory::firstOrFail();
        $sampleFile = ActivityFile::firstOrFail();

        $this->actingAs($mitra);
        $this->get('/dashboard')->assertRedirect('/mitra/dashboard');

        // 1. Menu & Fitur Mitra Karier / HRD
        $dash = $this->get('/mitra/dashboard')->assertOk();
        $dash->assertSee('Nadia Putri Mahardika')
            ->assertDontSee('/admin/dashboard')
            ->assertDontSee('/admin/users')
            ->assertDontSee('/dosen/dashboard')
            ->assertDontSee('/laporan-progres')
            ->assertDontSee('/admin/api-console');

        // Pencarian semantik & filter Prodi
        $this->get('/mitra/dashboard?q=cyber+security&prodi=Teknik+Informatika')->assertOk();

        // Verifikasi kredensial, dokumen cetak, portofolio publik & privasi opt-out
        $this->get('/verify/WB-SKPI-2026-NADIA1')->assertOk()->assertSee('TERVERIFIKASI SAH');
        $this->get('/credentials/WB-SKPI-2026-NADIA1/document')->assertOk();
        $this->get('/@nadia-putri')->assertOk()->assertSee('Nadia Putri Mahardika');
        $this->get('/@raka-mahendra')->assertOk()->assertSee('Profil Publik Dinonaktifkan');

        // Read-only Kurikulum SKPI
        $this->get('/kurikulum-skpi')->assertOk();
        $this->get("/kurikulum-skpi/{$category->id}")->assertOk();

        // Info Akun Mandiri (tanpa panel Token API)
        $this->get('/akun')->assertOk()->assertDontSee('Token Akses Integrasi');
        $this->put('/akun', [
            'name' => 'HRD TechCorp Indonesia',
            'email' => $mitra->email,
        ])->assertRedirect()->assertSessionHas('success');

        // 2. Pembatasan Akses Ketat Mitra
        $this->get('/admin/dashboard')->assertForbidden();
        $this->get('/admin/users')->assertForbidden();
        $this->get('/admin/api-console')->assertForbidden();
        $this->get('/dosen/dashboard')->assertForbidden();
        $this->get('/mahasiswa/dashboard')->assertForbidden();
        $this->get('/laporan-progres')->assertForbidden();
        $this->get('/kurikulum-skpi/create')->assertForbidden();
        $this->post('/kurikulum-skpi', [])->assertForbidden();
        $this->get("/kurikulum-skpi/{$category->id}/edit")->assertForbidden();
        $this->delete("/kurikulum-skpi/{$category->id}")->assertForbidden();
        $this->post('/akun/tokens', ['token_name' => 'mitra-illegal'])->assertForbidden();
        $this->get($sampleFile->temporarySignedPreviewUrl(5))->assertForbidden();
        $this->postJson('/api/v1/login', [
            'email' => $mitra->email,
            'password' => 'password123',
        ])->assertForbidden();

        // 3. Logout -> redirect ke /
        $this->get('/logout/verify')->assertOk();
        $this->post('/logout')->assertRedirect('/');
        $this->assertGuest();
    }
}
