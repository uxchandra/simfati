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
        // 1. Table general_checkups (Master)
        Schema::create('general_checkups', function (Blueprint $table) {
            $table->id();
            $table->string('checkup_code')->unique();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->unsignedBigInteger('part_id')->nullable();
            $table->dateTime('checkup_date');
            $table->unsignedBigInteger('user_id'); // inspector
            $table->enum('shift', ['morning', 'afternoon', 'night']);
            $table->enum('overall_status', ['good', 'problem', 'critical']);
            $table->text('notes')->nullable();
            $table->unsignedBigInteger('created_by');
            $table->timestamps();

            // Foreign keys
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('part_id')->references('id')->on('parts')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');

            // Index untuk performa
            $table->index(['checkup_date', 'overall_status']);
            $table->index(['machine_id', 'part_id']);
        });

        // 2. Table checkup_details (Detail per Check Item)
        Schema::create('checkup_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('general_checkup_id');
            $table->unsignedBigInteger('check_item_id');
            $table->enum('item_status', ['good', 'problem', 'critical', 'maintenance_needed']);
            $table->text('maintenance_notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('general_checkup_id')->references('id')->on('general_checkups')->onDelete('cascade');
            $table->foreign('check_item_id')->references('id')->on('check_items')->onDelete('cascade');

            // Prevent duplicate check item dalam 1 checkup
            $table->unique(['general_checkup_id', 'check_item_id']);
        });

        // 3. Table checkup_standards (Detail per Standard)
        Schema::create('checkup_standards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('checkup_detail_id');
            $table->unsignedBigInteger('check_standard_id');
            $table->enum('result', ['OK', 'NG']);
            $table->text('notes')->nullable();
            $table->timestamps();

            // Foreign keys
            $table->foreign('checkup_detail_id')->references('id')->on('checkup_details')->onDelete('cascade');
            $table->foreign('check_standard_id')->references('id')->on('check_standards')->onDelete('cascade');

            // Prevent duplicate standard dalam 1 checkup detail
            $table->unique(['checkup_detail_id', 'check_standard_id']);
        });

        // 4. Table checkup_photos (Dokumentasi Foto)
        Schema::create('checkup_photos', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('general_checkup_id');
            $table->string('photo_path');
            $table->string('photo_description')->nullable();
            $table->timestamp('uploaded_at');
            $table->timestamps();

            // Foreign key
            $table->foreign('general_checkup_id')->references('id')->on('general_checkups')->onDelete('cascade');

            // Index untuk performa
            $table->index('general_checkup_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checkup_photos');
        Schema::dropIfExists('checkup_standards');
        Schema::dropIfExists('checkup_details');
        Schema::dropIfExists('general_checkups');
    }
};