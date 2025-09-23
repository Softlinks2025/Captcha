<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // Create roles for 'web', 'api', and 'agent' guards
        foreach (['web', 'api', 'agent'] as $guard) {
            $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => $guard]);
            $userRole = Role::firstOrCreate(['name' => 'user', 'guard_name' => $guard]);
            $agentRole = Role::firstOrCreate(['name' => 'agent', 'guard_name' => $guard]);
            $pushSubadminRole = Role::firstOrCreate(['name' => 'push-subadmin', 'guard_name' => $guard]);
            $withdrawalSubadminRole = Role::firstOrCreate(['name' => 'withdrawal-subadmin', 'guard_name' => $guard]);
            $ticketManagerRole = Role::firstOrCreate(['name' => 'ticket-manager', 'guard_name' => $guard]);
        }

        // Define permissions
        $permissions = [
            'manage users',
            'view dashboard',
            'purchase plan',
            'manage push notifications',
            'manage withdrawals',
            'manage tickets',
        ];
        foreach (['web', 'api', 'agent'] as $guard) {
            foreach ($permissions as $perm) {
                Permission::firstOrCreate(['name' => $perm, 'guard_name' => $guard]);
            }
        }

        // Assign permissions to roles for all guards
        foreach (['web', 'api', 'agent'] as $guard) {
            $adminRole = Role::where('name', 'admin')->where('guard_name', $guard)->first();
            $userRole = Role::where('name', 'user')->where('guard_name', $guard)->first();
            $agentRole = Role::where('name', 'agent')->where('guard_name', $guard)->first();
            $pushSubadminRole = Role::where('name', 'push-subadmin')->where('guard_name', $guard)->first();
            $withdrawalSubadminRole = Role::where('name', 'withdrawal-subadmin')->where('guard_name', $guard)->first();
            $ticketManagerRole = Role::where('name', 'ticket-manager')->where('guard_name', $guard)->first();
            if ($adminRole) {
                $adminRole->givePermissionTo(Permission::where('guard_name', $guard)->pluck('name')->toArray());
            }
            if ($userRole) {
                $userRole->givePermissionTo('purchase plan');
            }
            if ($agentRole) {
                $agentRole->givePermissionTo('purchase plan');
            }
            if ($pushSubadminRole) {
                $pushSubadminRole->givePermissionTo('manage push notifications');
            }
            if ($withdrawalSubadminRole) {
                $withdrawalSubadminRole->givePermissionTo('manage withdrawals');
            }
            if ($ticketManagerRole) {
                $ticketManagerRole->givePermissionTo('manage tickets');
            }
        }
    }
} 