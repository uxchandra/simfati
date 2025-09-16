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
        // Tabel check_items
        Schema::create('check_items', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('machine_id')->nullable();
            $table->unsignedBigInteger('part_id')->nullable();
            $table->string('item_name');
            $table->timestamps();

            // Foreign key ke machines
            $table->foreign('machine_id')
                ->references('id')->on('machines')
                ->onDelete('cascade');

            // Foreign key ke parts
            $table->foreign('part_id')
                ->references('id')->on('parts')
                ->onDelete('cascade');
        });

        // Tabel check_standards
        Schema::create('check_standards', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('check_item_id');
            $table->string('standard_name'); // ganti dari standard_text
            $table->timestamps();

            // Foreign key ke check_items
            $table->foreign('check_item_id')
                ->references('id')->on('check_items')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('check_standards');
        Schema::dropIfExists('check_items');
    }
};
