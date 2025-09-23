<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->decimal('reward_per_level', 10, 2)->default(10.00)->after('captchas_per_level')
                  ->comment('Fixed reward amount given for each level completion');
        });
    }

    public function down()
    {
        Schema::table('subscription_plans', function (Blueprint $table) {
            $table->dropColumn('reward_per_level');
        });
    }
};
