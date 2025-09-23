<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;

class ResetDailyLevels extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'levels:reset-daily';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset all users daily levels to 0';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Starting daily level reset...');
        
        $users = User::all();
        $resetCount = 0;
        
        foreach ($users as $user) {
            $user->level = 0;
            $user->save();
            $resetCount++;
        }
        
        $this->info("Successfully reset levels for {$resetCount} users.");
        
        // Log the reset
        \Log::info("Daily level reset completed. Reset {$resetCount} users.");
        
        return 0;
    }
}
