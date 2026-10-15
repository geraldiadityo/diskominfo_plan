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
        Schema::table('indikator_realisasis', function (Blueprint $table) {
            $table->boolean('is_tercapai')->default(false)->after('nilai_realisasi');
            $table->boolean('is_verif')->default(false)->after('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('indikator_realisasis', function (Blueprint $table) {
            $table->dropColumn(['is_tercapai', 'is_verif']);
        });
    }
};
