<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class FixTicketManagerRoleSeeder extends Seeder
{
    public function run()
    {
        // Create the permission if it doesn't exist
        $permission = Permission::firstOrCreate([
            'name' => 'manage tickets',
            'guard_name' => 'web'
        ]);

        // Create the role if it doesn't exist
        $role = Role::firstOrCreate([
            'name' => 'ticket-manager',
            'guard_name' => 'web'
        ]);

        // Assign the permission to the role
        $role->givePermissionTo($permission);

        $this->command->info('Ticket manager role and permission have been set up.');
    }
}
