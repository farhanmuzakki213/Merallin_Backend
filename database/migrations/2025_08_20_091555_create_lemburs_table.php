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
        Schema::create('lembur', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->enum('jenis_hari', ['Kerja', 'Libur', 'Libur Nasional']);
            $table->enum('department', ['IT', 'Marketing']);
            $table->date('tanggal_lembur');
            $table->text('keterangan_lembur');
            $table->time('mulai_jam_lembur');
            $table->time('selesai_jam_lembur');
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
