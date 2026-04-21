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
        Schema::create('master_indikators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('parent_id')->nullable()->constrained('master_indikators')->cascadeOnDelete();
            $table->foreignId('skpd_id')->nullable()->constrained('skpds')->nullOnDelete();
            $table->foreignId('kategori_id')->nullable()->constrained('kategori_indikators')->nullOnDelete();
            $table->text('nama_indikator');
            $table->foreignId('satuan_id')->nullable()->constrained('satuan_indikators')->nullOnDelete();
            $table->text('kondisi_awal')->nullable();
            $table->boolean('is_measurable')->default(true);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_indikators');
    }
};
