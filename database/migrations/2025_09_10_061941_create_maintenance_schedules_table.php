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
        Schema::create('maintenance_schedules', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->unsignedBigInteger('part_id')->nullable();
            $table->string('schedule_name')->nullable();
            $table->integer('period_days');
            $table->date('start_date');
            $table->timestamps();

            // Foreign keys
            $table->foreign('machine_id')->references('id')->on('machines')->onDelete('cascade');
            $table->foreign('part_id')->references('id')->on('parts')->onDelete('cascade');

            // Index untuk performance
            $table->index(['machine_id', 'part_id']);
            $table->index('start_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('maintenance_schedules');
    }
};