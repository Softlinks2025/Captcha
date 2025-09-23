<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('agent_withdrawal_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('agent_id')->constrained('agents')->onDelete('cascade');
            $table->decimal('amount', 10, 2);
            $table->decimal('fee', 10, 2)->default(0);
            $table->decimal('final_withdrawal_amount', 10, 2);
            $table->string('upi_id');
            $table->enum('status', ['pending', 'approved', 'declined'])->default('pending');
            $table->timestamp('request_date')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('admin_id')->nullable();
            $table->string('remarks')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_withdrawal_requests');
    }
}; 