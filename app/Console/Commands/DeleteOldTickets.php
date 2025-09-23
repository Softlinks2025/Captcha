<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Ticket;
use Carbon\Carbon;

class DeleteOldTickets extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tickets:delete-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Delete tickets older than 30 days';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $cutoff = Carbon::now()->subDays(30);
        $count = Ticket::where('created_at', '<', $cutoff)->delete();
        $this->info("Deleted $count tickets older than 30 days.");
    }
} 