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
        Schema::create('parts', function (Blueprint $table) {
            $table->id();
            $table->string('part_code')->unique();
            $table->string('part_name');
            $table->string('part_type')->nullable();
            $table->foreignId('machine_id')->constrained('machines')->onDelete('cascade');
            $table->string('model')->nullable();
            $table->integer('quantity')->default(0);
            $table->string('process')->nullable();
            $table->string('customer')->nullable();
            $table->enum('status', ['active', 'inactive'])->default('active');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('parts');
    }
};
