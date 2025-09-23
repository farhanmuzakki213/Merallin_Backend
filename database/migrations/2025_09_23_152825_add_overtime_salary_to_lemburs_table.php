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
        Schema::table('lemburs', function (Blueprint $table) {
            // Menyimpan total jam lembur dalam format desimal untuk presisi
            $table->decimal('total_jam', 5, 2)->nullable()->after('selesai_jam_lembur');
            // Menyimpan nominal gaji lembur
            $table->decimal('gaji_lembur', 15, 2)->default(0)->after('total_jam');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lemburs', function (Blueprint $table) {
            $table->dropColumn('total_jam');
            $table->dropColumn('gaji_lembur');
        });
    }
};
