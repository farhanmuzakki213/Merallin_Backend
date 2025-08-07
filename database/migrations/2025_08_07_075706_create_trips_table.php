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
            $table->foreignId('user_id')->constrained()->onDelete('cascade'); // Driver
            $table->string('project_name');
            $table->string('license_plate');
            $table->integer('start_km');
            $table->integer('end_km')->nullable();

            // Data saat mulai trip
            $table->string('start_photo_path');
            $table->string('delivery_letter_path'); // Surat jalan
            $table->double('start_latitude', 10, 8);
            $table->double('start_longitude', 11, 8);
            $table->timestamp('started_at')->useCurrent();
            $table->enum('status', ['pengajuan', 'jalan', 'tiba', 'bongkar', 'selesai'])->default('pengajuan');

            // Data saat selesai trip
            $table->string('end_photo_path')->nullable();
            $table->string('end_delivery_letter_path')->nullable(); // Bukti bongkar
            $table->double('end_latitude', 10, 8)->nullable();
            $table->double('end_longitude', 11, 8)->nullable();
            $table->timestamp('ended_at')->nullable();

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
