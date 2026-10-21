<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('progul_realisasis', function (Blueprint $table) {
            $table->id();
            $table->foreignId('indikator_id')
                  ->constrained('progul_indikators')
                  ->cascadeOnDelete();
            $table->foreignId('skpd_id')
                  ->nullable()
                  ->constrained('skpds')
                  ->nullOnDelete();
            $table->year('tahun');
            $table->string('target_tahunan')->nullable();
            $table->string('nilai_realisasi')->nullable();
            $table->enum('status', ['draf', 'diajukan', 'disetujui', 'ditolak'])->default('draf');
            $table->text('catatan_bappeda')->nullable();
            $table->timestamp('submitted_at')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->timestamps();

            $table->unique(['indikator_id', 'skpd_id', 'tahun']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('progul_realisasis');
    }
};

