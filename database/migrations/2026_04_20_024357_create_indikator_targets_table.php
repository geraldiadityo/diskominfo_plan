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
        Schema::create('indikator_targets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')->constrained('master_indikators')->cascadeOnDelete();
            $table->integer('tahun');
            $table->text('nilai_target');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('indikator_targets');
    }
};
