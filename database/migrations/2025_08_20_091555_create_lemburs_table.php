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
        Schema::create('lemburs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('jenis_hari', ['Kerja', 'Libur', 'Libur Nasional']);
            $table->enum('department', ['Finance', 'Manager Operasional', 'HRD', 'IT', 'Admin']);
            $table->date('tanggal_lembur');
            $table->text('keterangan_lembur');
            $table->time('mulai_jam_lembur');
            $table->time('selesai_jam_lembur');

            // --- PENAMBAHAN KOLOM UNTUK CLOCK-IN/OUT DI SINI ---
            $table->dateTime('jam_mulai_aktual')->nullable();
            $table->string('foto_mulai_path')->nullable();
            $table->json('lokasi_mulai')->nullable();
            $table->dateTime('jam_selesai_aktual')->nullable();
            $table->string('foto_selesai_path')->nullable();
            $table->json('lokasi_selesai')->nullable();
            // --- AKHIR PENAMBAHAN KOLOM UNTUK CLOCK-IN/OUT DI SINI ---

            $table->enum('status_lembur', ['Ditolak', 'Diterima', 'Menunggu Persetujuan', 'Menunggu Konfirmasi Admin'])->default('Menunggu Persetujuan');
            $table->enum('persetujuan_direksi', ['Ditolak', 'Diterima', 'Menunggu Persetujuan'])->default('Menunggu Persetujuan');
            // $table->enum('persetujuan_manajer', ['Ditolak', 'Diterima', 'Menunggu Persetujuan'])->default('Menunggu Persetujuan');
            $table->text('alasan')->nullable();
            $table->uuid('uuid')->nullable()->unique();
            $table->string('file_path')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('lemburs');
    }
};
