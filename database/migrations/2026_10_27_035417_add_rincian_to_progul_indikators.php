<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('progul_indikators', function (Blueprint $table) {
            if (!Schema::hasColumn('progul_indikators', 'rincian')) {
                $table->text('rincian')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('progul_indikators', function (Blueprint $table) {
            if (Schema::hasColumn('progul_indikators', 'rincian')) {
                $table->dropColumn('rincian');
            }
        });
    }
};
