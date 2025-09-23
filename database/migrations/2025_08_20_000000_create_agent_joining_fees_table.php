<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        // Create the agent_joining_fees table only if it doesn't exist
        if (!Schema::hasTable('agent_joining_fees')) {
            Schema::create('agent_joining_fees', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->text('description')->nullable();
                $table->decimal('amount', 10, 2);
                $table->integer('validity_days')->default(365);
                $table->json('features')->nullable();
                $table->boolean('is_active')->default(true);
                $table->integer('sort_order')->default(0);
                $table->timestamps();
            });
        }

        // Add joining_fee_id to agent_plans if it doesn't exist
        Schema::table('agent_plans', function (Blueprint $table) {
            if (!Schema::hasColumn('agent_plans', 'joining_fee_id')) {
                $table->foreignId('joining_fee_id')
                    ->nullable()
                    ->constrained('agent_joining_fees')
                    ->onDelete('set null');
            }
        });

        // Add columns to agents table if they don't exist
        Schema::table('agents', function (Blueprint $table) {
            if (!Schema::hasColumn('agents', 'joining_fee_plan_id')) {
                $table->foreignId('joining_fee_plan_id')
                    ->nullable()
                    ->constrained('agent_joining_fees')
                    ->onDelete('set null');
            }
            
            if (!Schema::hasColumn('agents', 'joining_fee_amount')) {
                $table->decimal('joining_fee_amount', 10, 2)->default(0);
            }
            
            if (!Schema::hasColumn('agents', 'joining_fee_paid_at')) {
                $table->timestamp('joining_fee_paid_at')->nullable();
            }
            
            if (!Schema::hasColumn('agents', 'joining_fee_payment_id')) {
                $table->string('joining_fee_payment_id')->nullable();
            }
            
            if (!Schema::hasColumn('agents', 'joining_fee_payment_method')) {
                $table->string('joining_fee_payment_method')->nullable();
            }
            
            if (!Schema::hasColumn('agents', 'joining_fee_status')) {
                $table->string('joining_fee_status')->default('pending');
            }
        });
    }

    public function down()
    {
        // Don't drop tables in down migration to prevent data loss
        // Just remove the foreign keys
        Schema::table('agents', function (Blueprint $table) {
            if (Schema::hasColumn('agents', 'joining_fee_plan_id')) {
                $table->dropForeign(['joining_fee_plan_id']);
            }
        });

        Schema::table('agent_plans', function (Blueprint $table) {
            if (Schema::hasColumn('agent_plans', 'joining_fee_id')) {
                $table->dropForeign(['joining_fee_id']);
            }
        });
    }
};