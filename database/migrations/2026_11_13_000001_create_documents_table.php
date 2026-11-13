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
        Schema::create('documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_code', 64)->unique()->index();
            $table->string('document_name', 255);
            $table->string('document_hash', 64)->index(); // SHA-256 fingerprint
            $table->string('document_id', 66)->index();   // bytes32 keccak256 normalized ID (0x...)
            $table->foreignId('registered_by')->constrained('users')->cascadeOnDelete();
            $table->string('transaction_hash', 66)->nullable()->index();
            $table->unsignedBigInteger('block_number')->nullable();
            $table->unsignedBigInteger('gas_used')->nullable();
            $table->timestamp('registered_at')->nullable();
            $table->enum('registration_status', ['pending', 'confirmed', 'failed'])->default('pending')->index();
            $table->unsignedBigInteger('file_size')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};

