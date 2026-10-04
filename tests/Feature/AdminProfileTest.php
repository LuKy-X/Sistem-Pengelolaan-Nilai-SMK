<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AdminProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed();
    }

    private function getAdminUser(): User
    {
        $admin = User::whereHas('roles', fn ($q) => $q->where('code', 'ADMIN'))->first();
        $admin->update(['password' => Hash::make('password123')]);

        return $admin;
    }

    public function test_admin_can_view_profile_page(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->get(route('admin.profile.index'));

        $response->assertStatus(200);
        $response->assertSee('Profil &amp; Pengaturan Akun', false);
        $response->assertSee($admin->name);
        $response->assertSee($admin->username);
        $response->assertSee($admin->email);
    }

    public function test_admin_can_update_profile_info(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Admin Baru SMKN 2',
            'username' => 'admin_baru',
            'email' => 'admin_baru@smkn2kra.sch.id',
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success', 'Profil Anda berhasil diperbarui.');

        $this->assertDatabaseHas('users', [
            'id' => $admin->id,
            'name' => 'Admin Baru SMKN 2',
            'username' => 'admin_baru',
            'email' => 'admin_baru@smkn2kra.sch.id',
        ]);
    }

    public function test_admin_cannot_update_profile_with_duplicate_username_or_email(): void
    {
        $admin = $this->getAdminUser();

        // Ambil user lain
        $otherUser = User::where('id', '!=', $admin->id)->first();

        $response = $this->actingAs($admin)->put(route('admin.profile.update'), [
            'name' => 'Admin Tetap',
            'username' => $otherUser->username,
            'email' => $otherUser->email,
        ]);

        $response->assertSessionHasErrors(['username', 'email']);
    }

    public function test_admin_cannot_change_password_with_wrong_current_password(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password' => 'passwordsalah',
            'password' => 'secret123',
            'password_confirmation' => 'secret123',
        ]);

        $response->assertSessionHasErrors(['current_password']);
    }

    public function test_admin_cannot_change_password_with_mismatched_confirmation(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password' => 'password123',
            'password' => 'secret123',
            'password_confirmation' => 'berbeda123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_admin_cannot_change_password_with_short_password(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password' => 'password123',
            'password' => '123',
            'password_confirmation' => '123',
        ]);

        $response->assertSessionHasErrors(['password']);
    }

    public function test_admin_can_change_password_successfully(): void
    {
        $admin = $this->getAdminUser();

        $response = $this->actingAs($admin)->put(route('admin.profile.password'), [
            'current_password' => 'password123',
            'password' => 'newpassword123',
            'password_confirmation' => 'newpassword123',
        ]);

        $response->assertRedirect(route('admin.profile.index'));
        $response->assertSessionHas('success', 'Kata sandi berhasil diubah.');

        $admin->refresh();
        $this->assertTrue(Hash::check('newpassword123', $admin->password));
    }

    public function test_non_admin_cannot_access_admin_profile(): void
    {
        $teacher = User::whereHas('roles', fn ($q) => $q->where('code', 'TEACHER'))->first();

        $response = $this->actingAs($teacher)->get(route('admin.profile.index'));

        $response->assertStatus(403);
    }
}
