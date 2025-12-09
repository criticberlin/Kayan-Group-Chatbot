<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use TCG\Voyager\Models\Role;
use TCG\Voyager\Models\Permission;

class AssignProductPermissionsSeeder extends Seeder
{
    public function run()
    {
        // Get admin role
        $adminRole = Role::where('name', 'admin')->first();

        if (!$adminRole) {
            $this->command->error('Admin role not found!');
            return;
        }

        // Get all product permissions
        $productPermissions = Permission::where('table_name', 'products')->get();

        if ($productPermissions->isEmpty()) {
            $this->command->error('No product permissions found!');
            return;
        }

        // Assign permissions to admin role
        foreach ($productPermissions as $permission) {
            $adminRole->permissions()->syncWithoutDetaching([$permission->id]);
        }

        $this->command->info('Product permissions assigned to admin role successfully!');
        $this->command->info('Assigned permissions: ' . $productPermissions->pluck('key')->implode(', '));
    }
}
