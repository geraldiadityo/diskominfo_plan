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
        Schema::table('realisasi_kegiatans', function (Blueprint $table) {
            $table->text('alasan')->nullable();
            $table->text('solusi')->nullable();
            $table->boolean('is_verified')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('realisasi_kegiatans', function (Blueprint $table) {
            $table->dropColumn(['alasan', 'solusi', 'is_verified']);
        });
    }
};
