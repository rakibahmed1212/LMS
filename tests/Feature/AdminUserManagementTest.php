<?php

namespace Tests\Feature;

use App\Models\Role;
use App\Models\User;
use Database\Seeders\RoleAndPermissionSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AdminUserManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_super_admin_can_view_user_management(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());

        User::factory()->create(['name' => 'Parent User', 'email' => 'parent@example.test']);

        $this->actingAs($admin)
            ->get(route('admin.users.index'))
            ->assertOk()
            ->assertInertia(fn ($page) => $page
                ->component('Admin/Users')
                ->has('users.data')
                ->has('roles'));
    }

    public function test_parent_and_staff_cannot_access_admin_user_management(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());

        $tutor = User::factory()->create();
        $tutor->roles()->attach(Role::where('name', Role::TUTOR)->first());

        $this->actingAs($parent)
            ->get(route('admin.users.index'))
            ->assertForbidden();

        $this->actingAs($tutor)
            ->get(route('admin.users.index'))
            ->assertForbidden();
    }

    public function test_tutor_has_content_permissions_but_not_staff_management(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $tutor = User::factory()->create();
        $tutor->roles()->attach(Role::where('name', Role::TUTOR)->first());

        $this->assertTrue($tutor->hasPermission('create lesson'));
        $this->assertTrue($tutor->hasPermission('create quiz'));
        $this->assertFalse($tutor->hasPermission('manage staff'));
    }

    public function test_super_admin_can_create_user_with_roles(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());
        $tutorRole = Role::where('name', Role::TUTOR)->first();

        $this->actingAs($admin)
            ->post(route('admin.users.store'), [
                'name' => 'New Tutor',
                'email' => 'new.tutor@example.test',
                'phone' => '+8801700000000',
                'locale' => 'en',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role_ids' => [$tutorRole->id],
                'is_active' => true,
            ])
            ->assertRedirect(route('admin.users.index'));

        $user = User::where('email', 'new.tutor@example.test')->first();

        $this->assertNotNull($user);
        $this->assertTrue($user->hasRole(Role::TUTOR));
        $this->assertTrue($user->is_active);
    }

    public function test_non_admin_cannot_create_user(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $parent = User::factory()->create();
        $parent->roles()->attach(Role::where('name', Role::PARENT)->first());
        $parentRole = Role::where('name', Role::PARENT)->first();

        $this->actingAs($parent)
            ->post(route('admin.users.store'), [
                'name' => 'Blocked User',
                'email' => 'blocked@example.test',
                'locale' => 'en',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role_ids' => [$parentRole->id],
                'is_active' => true,
            ])
            ->assertForbidden();

        $this->assertNull(User::where('email', 'blocked@example.test')->first());
    }

    public function test_admin_user_create_requires_unique_email(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());
        $parentRole = Role::where('name', Role::PARENT)->first();
        User::factory()->create(['email' => 'taken@example.test']);

        $this->actingAs($admin)
            ->from(route('admin.users.index'))
            ->post(route('admin.users.store'), [
                'name' => 'Duplicate',
                'email' => 'taken@example.test',
                'locale' => 'en',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role_ids' => [$parentRole->id],
                'is_active' => true,
            ])
            ->assertSessionHasErrors('email');
    }

    public function test_super_admin_can_update_user_roles_and_status(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create();
        $admin->roles()->attach(Role::where('name', Role::SUPER_ADMIN)->first());

        $user = User::factory()->create(['is_active' => true]);
        $parentRole = Role::where('name', Role::PARENT)->first();
        $tutorRole = Role::where('name', Role::TUTOR)->first();
        $user->roles()->attach($parentRole);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $user), [
                'role_ids' => [$tutorRole->id],
                'is_active' => false,
            ])
            ->assertRedirect();

        $this->assertFalse($user->fresh()->is_active);
        $this->assertTrue($user->fresh()->hasRole(Role::TUTOR));
        $this->assertFalse($user->fresh()->hasRole(Role::PARENT));
    }

    public function test_admin_cannot_deactivate_self(): void
    {
        $this->seed(RoleAndPermissionSeeder::class);

        $admin = User::factory()->create(['is_active' => true]);
        $adminRole = Role::where('name', Role::SUPER_ADMIN)->first();
        $admin->roles()->attach($adminRole);

        $this->actingAs($admin)
            ->patch(route('admin.users.update', $admin), [
                'role_ids' => [$adminRole->id],
                'is_active' => false,
            ])
            ->assertStatus(422);

        $this->assertTrue($admin->fresh()->is_active);
    }
}
