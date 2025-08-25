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
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_name');
            $table->string('origin');
            $table->string('destination');

            // Data Awal Perjalanan
            $table->string('license_plate')->nullable();
            $table->integer('start_km')->nullable();
            $table->string('start_km_photo_path')->nullable();
            $table->enum('start_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('start_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('start_km_photo_verified_at')->nullable();
            $table->text('start_km_photo_rejection_reason')->nullable();

            // Data Setelah Muat
            $table->string('delivery_order_path')->nullable();
            $table->enum('delivery_order_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('delivery_order_verified_by')->nullable()->constrained('users');
            $table->timestamp('delivery_order_verified_at')->nullable();
            $table->text('delivery_order_rejection_reason')->nullable();

            $table->string('timbangan_kendaraan_photo_path')->nullable();
            $table->enum('timbangan_kendaraan_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('timbangan_kendaraan_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('timbangan_kendaraan_photo_verified_at')->nullable();
            $table->text('timbangan_kendaraan_photo_rejection_reason')->nullable();

            $table->string('segel_photo_path')->nullable();
            $table->enum('segel_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('segel_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('segel_photo_verified_at')->nullable();
            $table->text('segel_photo_rejection_reason')->nullable();

            // Data Proses Muat
            $table->string('muat_photo_path')->nullable();
            $table->enum('muat_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('muat_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('muat_photo_verified_at')->nullable();
            $table->text('muat_photo_rejection_reason')->nullable();

            // Data Proses Bongkar & Selesai
            $table->text('bongkar_photo_path')->nullable();
            $table->enum('bongkar_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('bongkar_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('bongkar_photo_verified_at')->nullable();
            $table->text('bongkar_photo_rejection_reason')->nullable();

            $table->integer('end_km')->nullable();
            $table->string('end_km_photo_path')->nullable();
            $table->enum('end_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('end_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('end_km_photo_verified_at')->nullable();
            $table->text('end_km_photo_rejection_reason')->nullable();

            // Surat jalan bisa diupload 2x, kita satukan saja
            $table->text('delivery_letter_path')->nullable();
            // Tambahkan kolom baru untuk verifikasi Surat Jalan AWAL (Initial)
            $table->enum('delivery_letter_initial_status', ['pending', 'approved', 'rejected'])->default('pending')->after('end_km_photo_rejection_reason');
            $table->foreignId('delivery_letter_initial_verified_by')->nullable()->constrained('users')->after('delivery_letter_initial_status');
            $table->timestamp('delivery_letter_initial_verified_at')->nullable()->after('delivery_letter_initial_verified_by');
            $table->text('delivery_letter_initial_rejection_reason')->nullable()->after('delivery_letter_initial_verified_at');

            // Tambahkan kolom baru untuk verifikasi Surat Jalan AKHIR (Final)
            $table->enum('delivery_letter_final_status', ['pending', 'approved', 'rejected'])->default('pending')->after('delivery_letter_initial_rejection_reason');
            $table->foreignId('delivery_letter_final_verified_by')->nullable()->constrained('users')->after('delivery_letter_final_status');
            $table->timestamp('delivery_letter_final_verified_at')->nullable()->after('delivery_letter_final_verified_by');
            $table->text('delivery_letter_final_rejection_reason')->nullable()->after('delivery_letter_final_verified_at');

            // Status Utama
            $table->enum('status_trip', ['tersedia', 'proses', 'selesai'])->default('tersedia');
            $table->enum('jenis_trip', ['muatan driver', 'muatan perusahan'])->default('muatan perusahan');

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
