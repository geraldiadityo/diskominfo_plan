<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_indikator_urusan', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')
                  ->constrained('progul_indikators')
                  ->cascadeOnDelete();
            $table->string('nama_urusan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_indikator_urusan');
    }
};

