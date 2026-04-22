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
        Schema::create('program_sub_kegiatans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('kegiatan_id')->constrained('program_kegiatans')->onDelete('restrict');
            $table->string('kode', 10);
            $table->string('kode_lengkap', 50)->unique();
            $table->text('nomenklatur');
            $table->text('kinerja')->nullable();
            $table->text('indikator')->nullable();
            $table->string('satuan', 100)->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_sub_kegiatans');
    }
};
