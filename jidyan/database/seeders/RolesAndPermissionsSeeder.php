<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        $permissions = [
            'update player profile',
            'upload media',
            'manage opportunities',
            'review applications',
            'verify users',
            'moderate content',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $roles = [
            'player' => ['update player profile', 'upload media'],
            'coach' => ['review applications'],
            'club_admin' => ['manage opportunities', 'review applications'],
            'agent' => ['update player profile'],
            'verifier' => ['verify users'],
            'admin' => $permissions,
        ];

        foreach ($roles as $role => $permissionsForRole) {
            $roleModel = Role::firstOrCreate(['name' => $role]);
            $roleModel->givePermissionTo($permissionsForRole);
        }
    }
}
