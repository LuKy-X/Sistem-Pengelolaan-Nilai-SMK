<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    public function test_login_screen_can_be_rendered(): void
    {
        $response = $this->get('/login');

        $response->assertStatus(200);
        $response->assertSee('Masuk ke Akun');
    }

    public function test_login_screen_renders_offline_and_shows_the_sponsor_bar(): void
    {
        $html = $this->get('/login')->assertOk()->getContent();

        $this->assertDoesNotMatchRegularExpression(
            '#(cdn\.tailwindcss|fonts\.googleapis|fonts\.gstatic|cdnjs\.cloudflare|unpkg\.com|jsdelivr|images\.unsplash)#i',
            $html,
            'The login screen must not reference a CDN; all assets are served locally.',
        );

        $this->assertSame(
            preg_match_all('#</div>#i', $html),
            preg_match_all('/<div\b/i', $html),
            'The login screen has unbalanced div tags.',
        );

        $this->assertStringContainsString('jhic-2026.webp', $html);
    }

    public function test_teacher_can_authenticate_using_username(): void
    {
        $response = $this->post('/login', [
            'login' => 'guru.agus',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.dashboard'));
    }

    public function test_teacher_can_authenticate_using_email(): void
    {
        $response = $this->post('/login', [
            'login' => 'agus@smk.test',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('teacher.dashboard'));
    }

    public function test_admin_can_authenticate_and_redirects_to_admin_dashboard(): void
    {
        $response = $this->post('/login', [
            'login' => 'admin',
            'password' => 'password123',
        ]);

        $this->assertAuthenticated();
        $response->assertRedirect(route('admin.dashboard'));
    }

    public function test_users_cannot_authenticate_with_invalid_password(): void
    {
        $response = $this->post('/login', [
            'login' => 'guru.agus',
            'password' => 'wrong-password',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_inactive_users_cannot_authenticate(): void
    {
        $user = User::where('username', 'guru.agus')->first();
        $user->update(['is_active' => false]);

        $response = $this->post('/login', [
            'login' => 'guru.agus',
            'password' => 'password123',
        ]);

        $this->assertGuest();
        $response->assertSessionHasErrors('login');
    }

    public function test_user_can_logout(): void
    {
        $user = User::where('username', 'guru.agus')->first();

        $response = $this->actingAs($user)->post('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));
    }

    public function test_user_can_logout_by_visiting_the_url_directly(): void
    {
        $user = User::where('username', 'guru.agus')->first();

        $response = $this->actingAs($user)->get('/logout');

        $this->assertGuest();
        $response->assertRedirect(route('login'));

        // The session was cleared, so the login form can be shown again.
        $this->get('/login')->assertOk()->assertSee('Masuk ke Akun');
    }
}
