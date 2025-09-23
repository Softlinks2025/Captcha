<?php

namespace App\Console\Commands;

use App\Models\User;
use Illuminate\Console\Command;
use Spatie\Permission\Models\Role;

class AssignAdminRole extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'role:assign-admin {email} {--create}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Assign admin role to a user';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $email = $this->argument('email');
        $createRole = $this->option('create');

        // Find or create admin role if needed
        $adminRole = Role::where('name', 'admin')->first();
        
        if (!$adminRole && $createRole) {
            $adminRole = Role::create(['name' => 'admin', 'guard_name' => 'web']);
            $this->info('Admin role created successfully.');
        } elseif (!$adminRole) {
            $this->error('Admin role does not exist. Use --create option to create it.');
            return 1;
        }

        // Find user by email
        $user = User::where('email', $email)->first();

        if (!$user) {
            $this->error("User with email {$email} not found.");
            return 1;
        }

        // Assign admin role
        $user->assignRole('admin');
        $this->info("Admin role assigned to user: {$user->name} ({$user->email})");

        return 0;
    }
}
