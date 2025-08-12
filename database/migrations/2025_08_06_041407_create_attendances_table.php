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
        Schema::create('attendances', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->string('photo_path');
            $table->double('latitude', 10, 8);
            $table->double('longitude', 11, 8);
            $table->enum('tipe_absensi', ['datang', 'pulang']);
            $table->enum('status_absensi', ['Tepat waktu', 'Terlambat']);
            $table->boolean('is_mocked')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
