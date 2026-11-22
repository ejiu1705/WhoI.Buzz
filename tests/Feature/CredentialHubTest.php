<?php

namespace Tests\Feature;

use App\Models\Activity;
use App\Models\IssuedCredential;
use App\Models\SkpiCategory;
use App\Models\User;
use App\Services\CredentialCryptoService;
use Database\Seeders\DatabaseSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use RuntimeException;
use Tests\TestCase;

class CredentialHubTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('private_vault');
        $this->seed(DatabaseSeeder::class);
    }

    public function test_guest_lands_on_whoibuzz_public_dashboard_with_login_register_and_floating_pill(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('whoibuzz-floating-pill')
            ->assertSee('images/whoibuzz-logo.png')
            ->assertSee('Masuk ke Akun')
            ->assertSee('Registrasi Akun Baru')
            ->assertDontSee('/api-client')
            ->assertDontSee('/admin/api-console')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');

        $this->get('/login')->assertOk()->assertSee('Masuk ke WhoI.Buzz');
        $this->get('/register')->assertOk()->assertSee('Daftar Akun WhoI.Buzz');
    }

    public function test_registration_creates_account_assigns_role_and_redirects_to_role_dashboard(): void
    {
        $response = $this->post('/register', [
            'name' => 'Ayu Lestari',
            'email' => 'ayu.lestari@campus.ac.id',
            'role' => 'mahasiswa',
            'nim' => '240411100088',
            'prodi_name' => 'Sistem Informasi',
            'semester' => 4,
            'password' => 'rahasia123',
            'password_confirmation' => 'rahasia123',
        ]);

        $response->assertRedirect('/mahasiswa/dashboard');
        $user = User::where('email', 'ayu.lestari@campus.ac.id')->firstOrFail();
        $this->assertTrue($user->hasRole('mahasiswa'));
        $this->assertDatabaseHas('student_profiles', ['nim' => '240411100088', 'user_id' => $user->id]);
    }

    public function test_role_dashboards_and_strict_rbac_403_isolation(): void
    {
        $admin = User::where('email', 'admin.kemahasiswaan@campus.ac.id')->firstOrFail();
        $dosen = User::where('email', 'dr.hendrawan@campus.ac.id')->firstOrFail();
        $mhs = User::where('email', 'nadia.putri@campus.ac.id')->firstOrFail();
        $mitra = User::where('email', 'hrd.mitra@techcorp.id')->firstOrFail();

        // Admin
        $this->actingAs($admin)->get('/admin/dashboard')->assertOk();
        $this->actingAs($admin)->get('/admin/users')->assertOk();
        $this->actingAs($admin)->get('/laporan-progres')->assertOk();

        // Dosen: own dashboard & laporan ok; admin dashboard forbidden (403)
        $this->actingAs($dosen)->get('/dosen/dashboard')->assertOk();
        $this->actingAs($dosen)->get('/laporan-progres')->assertOk();
        $this->actingAs($dosen)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($dosen)->get('/admin/users')->assertForbidden();

        // Mahasiswa: own dashboard ok; admin/dosen/laporan/create-kurikulum forbidden (403 BFLA)
        $this->actingAs($mhs)->get('/mahasiswa/dashboard')->assertOk();
        $this->actingAs($mhs)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($mhs)->get('/dosen/dashboard')->assertForbidden();
        $this->actingAs($mhs)->get('/laporan-progres')->assertForbidden();
        $this->actingAs($mhs)->get('/kurikulum-skpi/create')->assertForbidden();

        // Mitra: own dashboard ok; admin & mahasiswa dashboards forbidden (403)
        $this->actingAs($mitra)->get('/mitra/dashboard')->assertOk();
        $this->actingAs($mitra)->get('/admin/dashboard')->assertForbidden();
        $this->actingAs($mitra)->get('/mahasiswa/dashboard')->assertForbidden();
    }

    public function test_logout_verification_and_logout_lifecycle(): void
    {
        $mhs = User::where('email', 'nadia.putri@campus.ac.id')->firstOrFail();

        $this->actingAs($mhs)
            ->get('/logout/verify')
            ->assertOk()
            ->assertSee('Yakin Ingin Keluar dari Sesi?')
            ->assertSee($mhs->email);

        $this->actingAs($mhs)
            ->post('/logout', ['revoke_api_tokens' => 1])
            ->assertRedirect('/');

        $this->assertGuest();

        // Ensure unauthenticated demo-switch route is permanently removed
        $this->post('/demo-switch', ['email' => 'admin.kemahasiswaan@campus.ac.id'])->assertNotFound();

        // Ensure sensitive API console is strictly Admin-only and never exposed to guests or non-admins
        $this->get('/admin/api-console')->assertRedirect('/login');
        $this->actingAs($mhs)->get('/admin/api-console')->assertForbidden();
    }

    public function test_account_info_self_crud_and_admin_user_crud(): void
    {
        // Self Info Akun Update
        $mhs = User::where('email', 'bima.santoso@campus.ac.id')->firstOrFail();
        $this->actingAs($mhs)->get('/akun')->assertOk()->assertSee('Pengaturan Profil Akun');

        $this->actingAs($mhs)->put('/akun', [
            'name' => 'Bima Santoso Updated',
            'email' => 'bima.santoso@campus.ac.id',
            'headline' => 'Senior Laravel Engineer',
        ])->assertRedirect();

        $this->assertDatabaseHas('users', ['id' => $mhs->id, 'name' => 'Bima Santoso Updated']);

        // Admin User CRUD (Create -> Update -> Delete)
        $admin = User::where('email', 'admin.kemahasiswaan@campus.ac.id')->firstOrFail();
        $this->actingAs($admin)->post('/admin/users', [
            'name' => 'Dosen Baru CRUD',
            'email' => 'dosen.crud@campus.ac.id',
            'role' => 'dosen',
            'password' => 'password123',
            'password_confirmation' => 'password123',
        ])->assertRedirect('/admin/users');

        $created = User::where('email', 'dosen.crud@campus.ac.id')->firstOrFail();
        $this->assertTrue($created->hasRole('dosen'));

        $this->actingAs($admin)->put("/admin/users/{$created->id}", [
            'name' => 'Dosen CRUD Edited',
            'email' => 'dosen.crud@campus.ac.id',
            'role' => 'dosen',
        ])->assertRedirect('/admin/users');
        $this->assertDatabaseHas('users', ['id' => $created->id, 'name' => 'Dosen CRUD Edited']);

        $this->actingAs($admin)->delete("/admin/users/{$created->id}")->assertRedirect('/admin/users');
        $this->assertDatabaseMissing('users', ['id' => $created->id]);
    }

    public function test_policy_prevents_idor_between_two_dosens_on_kurikulum_skpi(): void
    {
        $dosenA = User::where('email', 'dr.hendrawan@campus.ac.id')->firstOrFail();
        $dosenB = User::where('email', 'dosen2@campus.ac.id')->firstOrFail();

        $catOwnedByB = SkpiCategory::where('created_by', $dosenB->id)->firstOrFail();
        $catOwnedByA = SkpiCategory::where('created_by', $dosenA->id)->firstOrFail();

        // Dosen A tries to edit Dosen B's category -> 403 Forbidden (Anti-IDOR)
        $this->actingAs($dosenA)->get("/kurikulum-skpi/{$catOwnedByB->id}/edit")->assertForbidden();

        // Dosen A edits own category -> 200 OK
        $this->actingAs($dosenA)->get("/kurikulum-skpi/{$catOwnedByA->id}/edit")->assertOk();
    }

    public function test_restful_api_v1_with_sanctum_bearer_token_lifecycle(): void
    {
        // Public GET works
        $this->getJson('/api/v1/skpi-categories')->assertOk()->assertJsonStructure(['data', 'links', 'meta']);

        // Protected POST without token -> 401
        $this->postJson('/api/v1/skpi-categories', [])->assertUnauthorized();

        // Login API -> receive Bearer token
        $login = $this->postJson('/api/v1/login', [
            'email' => 'admin.kemahasiswaan@campus.ac.id',
            'password' => 'password123',
        ])->assertOk()->assertJsonStructure(['token', 'token_type', 'user']);

        $token = $login->json('token');

        // Authenticated GET /me
        $this->withHeader('Authorization', "Bearer {$token}")
            ->getJson('/api/v1/me')
            ->assertOk()
            ->assertJsonPath('data.role', 'admin');

        // Authenticated POST create SKPI category -> 201
        $created = $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/skpi-categories', [
                'code' => 'SKPI-API-99',
                'name_id' => 'Kategori API Teruji',
                'name_en' => 'Verified API Category',
                'competency_domain' => 'Technical',
                'default_points' => 25,
                'level' => 'Nasional',
                'is_mandatory' => false,
            ])->assertCreated();

        $catId = $created->json('data.id');

        // Authenticated DELETE -> 204
        $this->withHeader('Authorization', "Bearer {$token}")
            ->deleteJson("/api/v1/skpi-categories/{$catId}")
            ->assertNoContent();

        // Logout API revokes token
        $this->withHeader('Authorization', "Bearer {$token}")
            ->postJson('/api/v1/logout')
            ->assertOk();

        $this->assertDatabaseCount('personal_access_tokens', 0);
    }

    public function test_cryptographic_qr_privacy_immutable_sha256_and_siakad_api(): void
    {
        $valid = IssuedCredential::where('registration_number', 'WB-SKPI-2026-NADIA1')->firstOrFail();
        $revoked = IssuedCredential::where('registration_number', 'WB-REVOKED-2026-999')->firstOrFail();

        $crypto = app(CredentialCryptoService::class);
        $this->assertTrue($crypto->verifyCredential($valid)['is_authentic']);
        $this->assertTrue($crypto->verifyCredential($revoked)['is_revoked']);

        $this->get('/verify/WB-SKPI-2026-NADIA1')->assertOk()->assertSee('TERVERIFIKASI SAH SECARA KRIPTOGRAFIS');
        $this->get('/verify/WB-REVOKED-2026-999')->assertOk()->assertSee('DICABUT / TIDAK BERLAKU');
        $this->get('/@nadia-putri')->assertOk()->assertSee('Nadia Putri Mahardika');
        $this->get('/@raka-mahendra')->assertOk()->assertSee('Profil Publik Dinonaktifkan');

        // SHA-256 anti-duplication
        $student = User::where('email', 'nadia.putri@campus.ac.id')->firstOrFail();
        $cat = SkpiCategory::firstOrFail();
        $img = imagecreatetruecolor(80, 40);
        ob_start();
        imagepng($img);
        $pngBytes = (string) ob_get_clean();
        imagedestroy($img);

        $payload = [
            'category_id' => $cat->id,
            'title_id' => 'Pelatihan Kriptografi',
            'title_en' => 'Cryptography Workshop',
            'organizer' => 'Cyber Lab',
            'role_in_activity' => 'Peserta',
            'activity_date' => now()->subDays(2)->format('Y-m-d'),
            'submit_mode' => 'submitted',
        ];

        $this->actingAs($student)
            ->post('/mahasiswa/activities', [...$payload, 'certificate_file' => UploadedFile::fake()->createWithContent('c1.png', $pngBytes)])
            ->assertRedirect('/mahasiswa/dashboard');

        $this->actingAs($student)
            ->post('/mahasiswa/activities', [...$payload, 'certificate_file' => UploadedFile::fake()->createWithContent('c2.png', $pngBytes)])
            ->assertSessionHasErrors('certificate_file');

        // SIAKAD JSON:API 1.1 is strictly Admin-only via Sanctum Bearer Token
        $this->actingAs($student)->getJson('/api/v1/siakad/students')->assertForbidden();
        auth('web')->logout();
        auth()->forgetGuards();
        $this->getJson('/api/v1/siakad/students')->assertUnauthorized();
        $admin = User::where('email', 'admin.kemahasiswaan@campus.ac.id')->firstOrFail();
        $adminToken = $admin->createToken('postman-test')->plainTextToken;
        $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->getJson('/api/v1/siakad/students')
            ->assertOk()
            ->assertHeader('Content-Type', 'application/vnd.api+json');

        // Immutable Approved Record protection
        $approved = Activity::where('status', 'approved')->firstOrFail();
        $this->expectException(RuntimeException::class);
        $approved->update(['awarded_points' => 999]);
    }
}
