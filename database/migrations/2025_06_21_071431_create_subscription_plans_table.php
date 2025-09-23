<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSubscriptionPlansTable extends Migration
{
    public function up()
    {
        Schema::create('subscription_plans', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('captcha_per_day');
            $table->integer('min_withdrawal_limit')->nullable();
            $table->decimal('cost', 10, 2);
            $table->string('earning_type')->nullable();
            $table->string('plan_type')->nullable();
            $table->string('icon')->nullable();
            $table->string('image')->nullable();
            $table->integer('caption_limit')->nullable();
            $table->json('earnings')->nullable();
            $table->integer('min_daily_earning')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subscription_plans');
    }
}
