<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $roleSuperAdmin = Role::firstOrNew(['name' => 'super-admin']);
        $roleAdmin = Role::firstOrNew(['name' => 'admin']);
        $roleManager = Role::firstOrNew(['name' => 'manager']);
        $roleSuperAdmin->save();
        $roleAdmin->save();
        $roleManager->save();

        $permissionCreateSuperAdmins = Permission::firstOrNew(['name' => 'create super-admin']);
        $permissionCreateSuperAdmins->assignRole($roleSuperAdmin);
        $permissionCreateSuperAdmins->save();

        $permissionCreateSuperAdmins = Permission::firstOrNew(['name' => 'create admin']);
        $permissionCreateSuperAdmins->assignRole($roleSuperAdmin);
        $permissionCreateSuperAdmins->save();

        $permissionCreateAdmins = Permission::firstOrNew(['name' => 'create manager']);
        $permissionCreateAdmins->assignRole($roleSuperAdmin);
        $permissionCreateAdmins->assignRole($roleAdmin);
        $permissionCreateAdmins->save();

        $permissionCreateAction = Permission::firstOrNew(['name' => 'create action']);
        $permissionCreateAction->assignRole($roleSuperAdmin);
        $permissionCreateAction->assignRole($roleAdmin);
        $permissionCreateAction->save();
    }
}
