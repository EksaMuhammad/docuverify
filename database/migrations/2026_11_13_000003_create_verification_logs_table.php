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
        Schema::create('verification_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->nullable()->constrained('documents')->nullOnDelete();
            $table->string('document_code', 64)->index();
            $table->string('uploaded_hash', 64)->index();
            $table->string('blockchain_hash', 64)->nullable();
            $table->enum('verification_status', ['VALID', 'MODIFIED', 'UNVERIFIABLE', 'TECHNICAL_ERROR'])->index();
            $table->unsignedInteger('processing_time_ms')->default(0);
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('verification_logs');
    }
};

