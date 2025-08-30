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
        Schema::create('vehicle_locations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('vehicle_id')->constrained()->onDelete('cascade');
            $table->foreignId('trip_id')->nullable()->constrained('trips')->onDelete('set null');
            $table->text('keterangan')->nullable();

            $table->text('start_location')->nullable();
            $table->string('standby_photo_path')->nullable();
            $table->enum('standby_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('standby_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('standby_photo_verified_at')->nullable();
            $table->text('standby_photo_rejection_reason')->nullable();

            $table->string('start_km_photo_path')->nullable();
            $table->enum('start_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('start_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('start_km_photo_verified_at')->nullable();
            $table->text('start_km_photo_rejection_reason')->nullable();

            $table->string('end_km_photo_path')->nullable();
            $table->enum('end_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('end_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('end_km_photo_verified_at')->nullable();
            $table->text('end_km_photo_rejection_reason')->nullable();

            $table->text('end_location')->nullable();

            $table->enum('status_vehicle_location', [
                'proses',
                'verifikasi gambar',
                'revisi gambar',
                'selesai'
            ])->default('proses');

            $table->enum('status_lokasi', [
                'stanby',
                'menuju lokasi',
                'sampai di lokasi'
            ])->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vehicle_locations');
    }
};
