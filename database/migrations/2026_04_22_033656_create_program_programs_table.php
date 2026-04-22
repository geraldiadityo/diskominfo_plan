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
        Schema::create('program_programs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('bidang_id')->constrained('program_bidangs')->onDelete('restrict');
            $table->string('kode', 10);
            $table->string('kode_lengkap', 30)->unique();
            $table->text('nomenklatur');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('program_programs');
    }
};
