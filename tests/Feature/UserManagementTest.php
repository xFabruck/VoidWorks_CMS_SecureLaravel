<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class UserManagementTest extends TestCase
{
    use RefreshDatabase;

    private function superAdmin(array $attributes = []): User
    {
        return User::factory()->create($attributes + ['role' => 'super_admin', 'status' => 'active']);
    }

    public function test_guest_cannot_open_user_administration(): void
    {
        $this->get(route('admin.users.index'))->assertRedirect(route('login'));
    }

    public function test_admin_without_superadmin_role_cannot_manage_users(): void
    {
        $this->actingAs(User::factory()->create(['role' => 'editor']))
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_superadmin_can_create_and_edit_user_without_exposing_password(): void
    {
        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin);

        $this->get(route('admin.users.create'))->assertOk();
        $this->post(route('admin.users.store'), [
            'name' => 'Persona CMS',
            'email' => 'persona@example.test',
            'password' => 'VerySecretPassword982',
            'password_confirmation' => 'VerySecretPassword982',
            'role' => 'admin',
            'status' => 'active',
        ])->assertRedirect(route('admin.users.index'));

        $created = User::query()->where('email', 'persona@example.test')->firstOrFail();
        $this->assertTrue(Hash::check('VerySecretPassword982', $created->password));
        $this->assertNotSame('VerySecretPassword982', $created->password);
        $this->assertArrayNotHasKey('password', $created->toArray());

        $this->get(route('admin.users.edit', $created))
            ->assertOk()
            ->assertDontSee('VerySecretPassword982')
            ->assertDontSee($created->password);

        $this->put(route('admin.users.update', $created), [
            'name' => 'Persona CMS Editada',
            'email' => 'persona@example.test',
            'password' => '',
            'password_confirmation' => '',
            'role' => 'super_admin',
            'status' => 'active',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', ['id' => $created->id, 'role' => 'super_admin', 'name' => 'Persona CMS Editada']);
        $this->assertTrue(Hash::check('VerySecretPassword982', $created->fresh()->password));
    }

    public function test_cannot_demote_or_suspend_last_active_superadmin(): void
    {
        $superAdmin = $this->superAdmin();
        $this->actingAs($superAdmin);

        $this->patch(route('admin.users.status', $superAdmin), ['status' => 'suspended'])
            ->assertSessionHasErrors('status');
        $this->put(route('admin.users.update', $superAdmin), [
            'name' => $superAdmin->name,
            'email' => $superAdmin->email,
            'password' => '',
            'password_confirmation' => '',
            'role' => 'admin',
            'status' => 'active',
        ])->assertSessionHasErrors('status');

        $this->assertDatabaseHas('users', ['id' => $superAdmin->id, 'role' => 'super_admin', 'status' => 'active']);
    }

    public function test_last_active_superadmin_can_be_changed_when_another_active_superadmin_exists(): void
    {
        $superAdmin = $this->superAdmin();
        $this->superAdmin();

        $this->actingAs($superAdmin)
            ->patch(route('admin.users.status', $superAdmin), ['status' => 'suspended'])
            ->assertRedirect(route('admin.users.index'));
    }

    public function test_suspended_and_blocked_accounts_cannot_login(): void
    {
        foreach (['suspended', 'blocked'] as $status) {
            $user = User::factory()->create(['status' => $status]);

            $this->post(route('login.store'), [
                'email' => $user->email,
                'password' => 'password',
            ])->assertSessionHasErrors('email');
        }
    }

    public function test_suspending_an_existing_session_revokes_admin_access(): void
    {
        $user = User::factory()->create();
        $this->actingAs($user);

        $user->update(['status' => 'suspended']);
        $this->actingAs($user->fresh());

        $this->get(route('admin.dashboard'))
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_superadmin_can_set_active_suspended_and_blocked_statuses(): void
    {
        $actor = $this->superAdmin();
        $managed = User::factory()->create();

        $this->actingAs($actor);
        foreach (['suspended', 'blocked', 'active'] as $status) {
            $this->patch(route('admin.users.status', $managed), ['status' => $status])
                ->assertRedirect(route('admin.users.index'));
            $this->assertDatabaseHas('users', ['id' => $managed->id, 'status' => $status]);
        }
    }

    public function test_password_validation_rejects_weak_or_unconfirmed_password(): void
    {
        $this->actingAs($this->superAdmin());

        $this->post(route('admin.users.store'), [
            'name' => 'Cuenta inválida',
            'email' => 'weak@example.test',
            'password' => 'weak',
            'password_confirmation' => 'different',
            'role' => 'admin',
            'status' => 'active',
        ])->assertSessionHasErrors('password');
    }
}
