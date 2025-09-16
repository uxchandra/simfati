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
        // 1. Table repair_requests (Master)
        Schema::create('repair_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_code')->unique();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->unsignedBigInteger('part_id')->nullable();
            $table->text('problem_description');
            $table->enum('status', ['pending', 'in_progress', 'completed', 'cancelled'])->default('pending');
            $table->unsignedBigInteger('requested_by');
            $table->dateTime('requested_at');
            $table->timestamps();

            // Foreign keys
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('part_id')->references('id')->on('parts')->onDelete('cascade');
            $table->foreign('requested_by')->references('id')->on('users')->onDelete('cascade');

            // Index untuk performa
            $table->index(['status', 'requested_at']);
            $table->index(['machine_id', 'part_id']);
            $table->index('requested_by');
        });

        // 2. Table repair_photos (Optional photos)
        Schema::create('repair_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('repair_request_id');
            $table->string('photo_path');
            $table->string('photo_description')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamps();

            // Foreign key
            $table->foreign('repair_request_id')->references('id')->on('repair_requests')->onDelete('cascade');

            // Index untuk performa
            $table->index('repair_request_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('repair_photos');
        Schema::dropIfExists('repair_requests');
    }
};