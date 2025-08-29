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
        Schema::create('bbm_kendaraan', function (Blueprint $table) {
            $table->id();

            // Kunci asing untuk user dan kendaraan
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained('vehicles')->onDelete('cascade');

            // Field untuk foto Kilometer Awal
            $table->string('start_km_photo_path')->nullable();
            $table->enum('start_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('start_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('start_km_photo_verified_at')->nullable();
            $table->text('start_km_photo_rejection_reason')->nullable();

            // Field untuk foto Kilometer Akhir
            $table->string('end_km_photo_path')->nullable();
            $table->enum('end_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('end_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('end_km_photo_verified_at')->nullable();
            $table->text('end_km_photo_rejection_reason')->nullable();

            // Field untuk foto Nota Pengisian
            $table->string('nota_pengisian_photo_path')->nullable();
            $table->enum('nota_pengisian_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('nota_pengisian_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('nota_pengisian_photo_verified_at')->nullable();
            $table->text('nota_pengisian_photo_rejection_reason')->nullable();

            $table->enum('status_bbm_kendaraan', [
                'proses',
                'verifikasi gambar',
                'revisi gambar',
                'selesai'
            ])->default('proses');

            $table->timestamps(); // Ini akan membuat kolom created_at dan updated_at
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bbm_kendaraan');
    }
};
