<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateWithdrawalRequestsTable extends Migration
{
    public function up()
    {
        Schema::create('withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('final_withdrawal_amount', 10, 2);
            $table->string('upi_id');
            $table->string('service_type')->default('UPI');
            $table->string('status')->default('pending'); // pending, approved, declined
            $table->timestamp('request_date')->useCurrent();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('withdrawal_requests');
    }
}
