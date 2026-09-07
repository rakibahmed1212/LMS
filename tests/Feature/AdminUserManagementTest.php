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
