<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_indikators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('program_id')
                  ->constrained('progul_programs')
                  ->cascadeOnDelete();
            $table->text('nama_indikator');
            $table->text('detail')->nullable();
            $table->integer('urutan')->default(0);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_indikators');
    }
};

