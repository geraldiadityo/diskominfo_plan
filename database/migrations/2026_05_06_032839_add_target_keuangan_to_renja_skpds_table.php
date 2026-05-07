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
        Schema::table('renja_skpds', function (Blueprint $table) {
            //
            $table->unsignedBigInteger('target_keuangan')->default(0)->after('pagu_anggaran');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('renja_skpds', function (Blueprint $table) {
            //
            $table->dropColumn('target_keuangan');
        });
    }
};
