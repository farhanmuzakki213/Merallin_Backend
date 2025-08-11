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
            $table->string('license_plate')->nullable();
            $table->string('start_km_photo_path')->nullable();
            $table->integer('start_km')->nullable();
            $table->string('end_km_photo_path')->nullable();
            $table->integer('end_km')->nullable();
            $table->string('origin');
            $table->string('destination');

            // Data saat mulai trip
            $table->string('start_photo_path')->nullable();
            $table->string('delivery_letter_path')->nullable();
            $table->double('start_latitude', 10, 8)->nullable();
            $table->double('start_longitude', 11, 8)->nullable();
            $table->timestamp('started_at')->nullable();
            $table->enum('status_trip', ['pengajuan', 'acc', 'done'])->default('pengajuan');
            $table->enum('status_lokasi', ['on site', 'tiba', 'dalam perjalanan'])->default('dalam perjalanan')->nullable();
            $table->enum('status_muatan', ['bongkar', 'muat'])->nullable();

            // Data saat selesai trip
            $table->string('end_photo_path')->nullable();
            $table->string('end_delivery_letter_path')->nullable();
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
