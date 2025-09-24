<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // Hapus kolom lama
            $table->dropColumn(['machine_code', 'machine_name', 'section', 'status']);
        });

        Schema::table('machines', function (Blueprint $table) {
            // Relasi kategori
            $table->foreignId('category_id')->after('id')->constrained('machine_categories')->onDelete('restrict');

            // Status baru
            $table->enum('status', ['active', 'inactive'])->default('active')->after('category_id');

            // Atribut umum
            $table->string('kode', 50)->unique()->after('status');
            $table->text('description')->nullable()->after('kode');
            $table->string('kapasitas', 100)->nullable();
            $table->string('model', 100)->nullable();
            $table->string('tahun_pembuatan', 10)->nullable();
            $table->string('nomor_seri', 100)->nullable()->index();
            $table->string('power', 100)->nullable();
            $table->string('tgl_instal', 100)->nullable();
            $table->text('keterangan')->nullable();

            // Atribut stamping (nullable untuk kategori lain)
            $table->string('capacity_kn', 100)->nullable();
            $table->string('slide_stroke', 100)->nullable();
            $table->string('stroke_per_minute', 100)->nullable(); // SPM
            $table->string('die_height', 100)->nullable();
            $table->string('slide_adjustment', 100)->nullable();
            $table->string('slide_area', 100)->nullable();
            $table->string('bolster_area', 100)->nullable();
            $table->string('main_motor', 100)->nullable();
            $table->string('req_air_pressure', 100)->nullable();
            $table->string('max_upper_die_weight', 100)->nullable();
            $table->string('power_source', 100)->nullable();
            $table->string('braking_time', 100)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('machines', function (Blueprint $table) {
            // Drop semua kolom baru
            $table->dropForeign(['category_id']);
            $table->dropColumn([
                'category_id',
                'status',
                'kode',
                'description',
                'kapasitas',
                'model',
                'tahun_pembuatan',
                'nomor_seri',
                'power',
                'tgl_instal',
                'keterangan',
                'capacity_kn',
                'slide_stroke',
                'stroke_per_minute',
                'die_height',
                'slide_adjustment',
                'slide_area',
                'bolster_area',
                'main_motor',
                'req_air_pressure',
                'max_upper_die_weight',
                'power_source',
                'braking_time',
            ]);

            // Balikin kolom lama
            $table->string('machine_code', 50)->unique();
            $table->string('machine_name', 100);
            $table->string('section', 100);
            $table->enum('status', ['active', 'maintenance', 'inactive'])->default('active');
        });
    }
};
