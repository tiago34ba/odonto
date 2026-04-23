<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_me_requires_authentication(): void
    {
        $response = $this->getJson('/api/auth/me');

        $response->assertStatus(401);
    }

    public function test_api_login_validates_required_fields(): void
    {
        $response = $this->postJson('/api/login', []);

        $response->assertStatus(422)
            ->assertJsonValidationErrors(['login', 'password']);
    }

    public function test_saas_admin_can_log_in_without_access_group(): void
    {
        User::factory()->create([
            'name' => 'saasadmin',
            'email' => 'saasadmin@odonto.local',
            'username' => null,
            'tipo' => 'saas_admin',
            'grupo_acesso_id' => null,
            'ativo' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'login' => 'saasadmin',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.tipo', 'saas_admin')
            ->assertJsonPath('user.grupo_acesso_id', null)
            ->assertJsonPath('user.grupo_acesso_nome', null);
    }

    public function test_user_can_log_in_with_username(): void
    {
        User::factory()->create([
            'name' => 'Admin',
            'username' => 'admin.odonto',
            'email' => 'admin.odonto@ssait.local',
            'tipo' => 'saas_admin',
            'grupo_acesso_id' => null,
            'ativo' => true,
        ]);

        $response = $this->postJson('/api/login', [
            'login' => 'admin.odonto',
            'password' => 'password',
        ]);

        $response->assertOk()
            ->assertJsonPath('user.email', 'admin.odonto@ssait.local')
            ->assertJsonPath('user.username', 'admin.odonto')
            ->assertJsonPath('user.tipo', 'saas_admin');
    }
}
