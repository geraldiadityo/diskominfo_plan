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
        Schema::table('transaksi_pendapatans', function (Blueprint $table) {
            if (Schema::hasColumn('transaksi_pendapatans', 'nomor_bukti')) {
                $table->dropColumn('nomor_bukti');
            }
            $table->string('bukti_dukung')->nullable()->after('jumlah_setor');
            $table->boolean('is_verif')->default(false)->after('keterangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaksi_pendapatans', function (Blueprint $table) {
            $table->dropColumn(['bukti_dukung', 'is_verif']);
            $table->string('nomor_bukti')->nullable()->after('jumlah_setor');
        });
    }
};

