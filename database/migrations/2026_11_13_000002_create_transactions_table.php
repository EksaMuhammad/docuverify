<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('documents')->cascadeOnDelete();
            $table->string('transaction_hash', 66)->unique()->index();
            $table->string('transaction_type', 32)->default('register');
            $table->string('network', 32)->default('hardhat_local');
            $table->unsignedBigInteger('gas_used')->nullable();
            $table->string('gas_price', 64)->nullable();
            $table->unsignedBigInteger('block_number')->nullable()->index();
            $table->enum('transaction_status', ['pending', 'confirmed', 'failed'])->default('pending')->index();
            $table->json('raw_receipt')->nullable();
            $table->timestamp('confirmed_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};

