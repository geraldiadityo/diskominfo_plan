<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progul_indikators', function (Blueprint $table) {
            $table->foreignId('sub_kegiatan_id')
                  ->nullable()
                  ->after('program_id')
                  ->constrained('program_sub_kegiatans')
                  ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('progul_indikators', function (Blueprint $table) {
            $table->dropForeign(['sub_kegiatan_id']);
            $table->dropColumn('sub_kegiatan_id');
        });
    }
};

