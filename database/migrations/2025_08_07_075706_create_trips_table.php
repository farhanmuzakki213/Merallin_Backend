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
            // Pembuatan Tugas TRIP di Admin Dashboard
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('project_name');
            $table->text('origin'); // Menyimpan koordinat awal dan alamat lengkap dalam array
            $table->text('destination'); // Menyimpan koordinat akhir dan alamat lengkap dalam array
            $table->time('slot_time'); //waktu deadline kendaraan muat barang
            $table->enum('jenis_berat', ['CDDL', 'CDDS', 'CDE']); // CDDL = 8 TON LEBIH, CDDS = 8 TON KURANG, CDE = 4 TON KURANG
            $table->integer('jumlah_gudang_muat')->default(1);
            $table->integer('jumlah_gudang_bongkar')->default(1);

            // Data Awal Perjalanan (updateStart)
            $table->foreignId('vehicle_id')->nullable()->constrained('vehicles')->onDelete('cascade');
            $table->integer('start_km')->nullable();
            $table->string('start_km_photo_path')->nullable();
            $table->enum('start_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('start_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('start_km_photo_verified_at')->nullable();
            $table->text('start_km_photo_rejection_reason')->nullable();

            // Data Persiapan Muat & Proses Muat & Selesai Muat (updateAfterLoading)
            $table->string('km_muat_photo_path')->nullable();
            $table->enum('km_muat_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('km_muat_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('km_muat_photo_verified_at')->nullable();
            $table->text('km_muat_photo_rejection_reason')->nullable();

            $table->string('kedatangan_muat_photo_path')->nullable();
            $table->enum('kedatangan_muat_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('kedatangan_muat_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('kedatangan_muat_photo_verified_at')->nullable();
            $table->text('kedatangan_muat_photo_rejection_reason')->nullable();

            $table->string('delivery_order_photo_path')->nullable();
            $table->enum('delivery_order_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('delivery_order_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('delivery_order_photo_verified_at')->nullable();
            $table->text('delivery_order_photo_rejection_reason')->nullable();

            $table->text('muat_photo_path')->nullable(); //berisi foto proses muat dan selesai muat yang disimpan di array
            $table->enum('muat_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('muat_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('muat_photo_verified_at')->nullable();
            $table->text('muat_photo_rejection_reason')->nullable();

            // Data Setelah Selesai Muat (uploadTripDocuments)
            $table->enum('delivery_letter_initial_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('delivery_letter_initial_verified_by')->nullable()->constrained('users');
            $table->timestamp('delivery_letter_initial_verified_at')->nullable();
            $table->text('delivery_letter_initial_rejection_reason')->nullable();

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

            // Data Persiapan Bongkar & Proses Bongkar & Selesai Bongkar (updateFinish)

            $table->integer('end_km')->nullable();
            $table->string('end_km_photo_path')->nullable();
            $table->enum('end_km_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('end_km_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('end_km_photo_verified_at')->nullable();
            $table->text('end_km_photo_rejection_reason')->nullable();

            $table->string('kedatangan_bongkar_photo_path')->nullable();
            $table->enum('kedatangan_bongkar_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('kedatangan_bongkar_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('kedatangan_bongkar_photo_verified_at')->nullable();
            $table->text('kedatangan_bongkar_photo_rejection_reason')->nullable();

            $table->text('bongkar_photo_path')->nullable(); //berisi foto proses bongkar dan selesai bongkar yang disimpan di array
            $table->enum('bongkar_photo_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('bongkar_photo_verified_by')->nullable()->constrained('users');
            $table->timestamp('bongkar_photo_verified_at')->nullable();
            $table->text('bongkar_photo_rejection_reason')->nullable();

            $table->enum('delivery_letter_final_status', ['pending', 'approved', 'rejected'])->default('pending');
            $table->foreignId('delivery_letter_final_verified_by')->nullable()->constrained('users');
            $table->timestamp('delivery_letter_final_verified_at')->nullable();
            $table->text('delivery_letter_final_rejection_reason')->nullable();

            // Surat jalan bisa diupload 2x, 1x ketika selesai muat dan 1x ketika selesai bongkar
            $table->text('delivery_letter_path')->nullable();

            // Status Utama
            $table->enum('status_trip', [
                'tersedia',
                'proses',
                'verifikasi gambar',
                'revisi gambar',
                'selesai'
            ])->default('tersedia');
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
