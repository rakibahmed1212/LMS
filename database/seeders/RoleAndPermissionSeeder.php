<?php

namespace Database\Seeders;

use App\Models\Permission;
use App\Models\Role;
use Illuminate\Database\Seeder;

class RoleAndPermissionSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            // content
            'manage content', 'create lesson', 'edit lesson', 'delete lesson',
            'create quiz', 'create assignment', 'upload worksheet',
            // students & families
            'manage students', 'view students',
            // subscriptions & billing
            'manage subscriptions', 'view subscriptions', 'manage payments', 'view payments',
            // reporting
            'view reports', 'view analytics',
            // admin
            'manage staff', 'manage plans', 'view audit logs',
        ];

        foreach ($permissions as $name) {
            Permission::firstOrCreate(['name' => $name, 'label' => str($name)->title()]);
        }

        $roles = [
            Role::SUPER_ADMIN => 'Super Admin',
            Role::TUTOR => 'Tutor',
            Role::CONTENT_MANAGER => 'Content Manager',
            Role::PARENT => 'Parent',
            Role::STUDENT => 'Student',
        ];

        foreach ($roles as $name => $label) {
            Role::firstOrCreate(['name' => $name, 'label' => $label]);
        }

        // Super admin implicitly owns everything; still attach all for clarity in audits.
        Role::where('name', Role::SUPER_ADMIN)->first()->permissions()->sync(Permission::pluck('id'));

        Role::where('name', Role::TUTOR)->first()->permissions()->sync(
            Permission::whereIn('name', ['create lesson', 'edit lesson', 'create quiz', 'create assignment', 'upload worksheet', 'view students'])->pluck('id')
        );

        Role::where('name', Role::CONTENT_MANAGER)->first()->permissions()->sync(
            Permission::whereIn('name', ['create lesson', 'edit lesson', 'upload worksheet', 'create quiz', 'create assignment'])->pluck('id')
        );

        Role::where('name', Role::PARENT)->first()->permissions()->sync(
            Permission::whereIn('name', ['manage students', 'view students', 'view payments'])->pluck('id')
        );
    }
}
