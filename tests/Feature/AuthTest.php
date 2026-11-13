<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');
        $response->assertStatus(200);
        $response->assertSee('Admin Portal Sign In');
    }

    public function test_users_can_authenticate_using_the_login_screen(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@docuverify.test',
            'password' => bcrypt('password123'),
            'role' => 'admin',
        ]);

        $response = $this->post('/login', [
            'email' => 'admin@docuverify.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $user->id,
            'activity' => 'LOGIN',
        ]);
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $user = User::factory()->create([
            'email' => 'admin@docuverify.test',
            'password' => bcrypt('password123'),
        ]);

        $this->post('/login', [
            'email' => 'admin@docuverify.test',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
    }

    public function test_unauthenticated_users_are_redirected_from_admin_routes(): void
    {
        $response = $this->get('/admin/dashboard');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/documents');
        $response->assertRedirect('/login');

        $response = $this->get('/admin/research');
        $response->assertRedirect('/login');
    }

    public function test_authenticated_user_can_logout(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect('/login');
    }
}

