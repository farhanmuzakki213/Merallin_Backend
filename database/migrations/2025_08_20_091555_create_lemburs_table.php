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
            $table->enum('status_lembur', ['Ditolak', 'Diterima', 'Menunggu Persetujuan'])->default('Menunggu Persetujuan');
            $table->enum('persetujuan_direksi', ['Ditolak', 'Diterima', 'Menunggu Persetujuan'])->default('Menunggu Persetujuan');
            $table->enum('persetujuan_manajer', ['Ditolak', 'Diterima', 'Menunggu Persetujuan'])->default('Menunggu Persetujuan');
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
