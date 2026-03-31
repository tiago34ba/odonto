<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RegistrationTest extends TestCase
{
    use RefreshDatabase;

    public function test_registration_screen_can_be_rendered(): void
    {
        $response = $this->get('/register');

        $response->assertStatus(200);
    }

    public function test_new_users_can_register(): void
    {
        $response = $this->post('/register', [
            'nome' => 'Test User',
            'email' => 'test@example.com',
            'password' => 'SenhaForte@123',
            'password_confirmation' => 'SenhaForte@123',
        ]);

        $response->assertRedirect(route('dashboard', absolute: false));
        $this->assertDatabaseHas((new User())->getTable(), [
            'email' => 'test@example.com',
            'nome' => 'Test User',
        ]);
    }
}
