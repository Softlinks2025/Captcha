<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Check if the users table exists and is empty
        if (Schema::hasTable('users') && \App\Models\User::count() === 0) {
            $this->seedDatabase();
        }
    }

    /**
     * Seed the application's database.
     */
    protected function seedDatabase(): void
    {
        Artisan::call('db:seed', ['--class' => 'DatabaseSeeder']);
    }
}
