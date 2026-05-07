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
            $table->decimal('target_fisik', 5, 2)->default(100)->after('target_keuangan');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('renja_skpds', function (Blueprint $table) {
            //
            $table->dropColumn('target_fisik');
        });
    }
};
