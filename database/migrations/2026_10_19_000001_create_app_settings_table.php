<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('app_settings', function (Blueprint $table) {
            $table->id();
            $table->string('app_name')->default('Dashboard Kota Sungai Penuh');
            $table->string('app_logo')->nullable();
            $table->timestamps();
        });

        // Seed satu baris awal
        DB::table('app_settings')->insert([
            'app_name' => 'Dashboard Kota Sungai Penuh',
            'app_logo' => null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_settings');
    }
};

