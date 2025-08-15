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
        Schema::create('trips', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->onDelete('set null'); // Driver, onDelete('set null') lebih aman
            $table->string('project_name');
            $table->string('origin');
            $table->string('destination');

            // Data Awal Perjalanan
            $table->string('license_plate')->nullable();
            $table->integer('start_km')->nullable();
            $table->string('start_km_photo_path')->nullable();

            // Data Proses Muat
            $table->string('muat_photo_path')->nullable();

            // Data Proses Bongkar & Selesai
            $table->string('bongkar_photo_path')->nullable();
            $table->integer('end_km')->nullable();
            $table->string('end_km_photo_path')->nullable();

            // Surat jalan bisa diupload 2x, kita satukan saja
            $table->string('delivery_letter_path')->nullable();

            // Status Utama
            $table->enum('status_trip', ['tersedia', 'proses', 'selesai'])->default('tersedia');

            // Status Detail (mengikuti alur)
            $table->enum('status_lokasi', [
                'menuju lokasi muat',
                'di lokasi muat',
                'menuju lokasi bongkar',
                'di lokasi bongkar'
            ])->nullable();

            $table->enum('status_muatan', [
                'kosong',
                'proses muat',
                'selesai muat',
                'termuat',
                'proses bongkar',
                'selesai bongkar'
            ])->nullable();

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('trips');
    }
};
