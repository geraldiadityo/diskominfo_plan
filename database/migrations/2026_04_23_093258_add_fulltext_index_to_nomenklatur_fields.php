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

        Schema::table('program_programs', function (Blueprint $table) {
            //
            $table->fullText('nomenklatur');
        });

        Schema::table('program_kegiatans', function (Blueprint $table) {
            //
            $table->fullText('nomenklatur');
        });

        Schema::table('program_sub_kegiatans', function (Blueprint $table) {
            //
            $table->fullText('nomenklatur');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('program_programs', function (Blueprint $table) {
            $table->dropFullText(['nomenklatur']);
        });
        Schema::table('program_kegiatans', function (Blueprint $table) {
            $table->dropFullText(['nomenklatur']);
        });
        Schema::table('program_sub_kegiatans', function (Blueprint $table) {
            $table->dropFullText(['nomenklatur']);
        });
    }
};
