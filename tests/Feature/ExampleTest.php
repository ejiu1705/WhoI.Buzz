<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_root_displays_whoibuzz_public_dashboard_with_login_and_register_options(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('WhoI.Buzz')
            ->assertSee('Masuk ke Akun')
            ->assertSee('Registrasi Akun Baru');
    }
}
