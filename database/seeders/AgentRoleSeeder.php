<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

class AgentRoleSeeder extends Seeder
{
    public function run(): void
    {
        // Create agent role if it doesn't exist for the agent guard
        $agentRole = Role::firstOrCreate(
            ['name' => 'agent'],
            ['guard_name' => 'agent']
        );

        $this->command->info('Agent role created successfully for agent guard!');
    }
}
