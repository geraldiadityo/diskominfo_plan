<?php

namespace Database\Seeders;

use App\Models\Skpd;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class AdminSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        //
        $skpd = Skpd::firstOrCreate(
            ['kode_skpd' => 'DISKOMINFO'],
            ['nama_skpd' => 'DInas Komunikasi, Infomatika Dan Statistik']
        );


        if (!User::where('email', 'admin@diskominfo.com')->exists()) {
            User::create([
                'email' => 'admin@diskominfo.com',
                'name' => 'Super Admin',
                'password' => Hash::make('password@123'),
                'role' => 'ADMIN',
                'skpd_id' => $skpd->id,
            ]);

            $this->command->info('Super admin was successfully created');
        } else {
            $this->command->info('Super admin user has already exists');
        }
    }
}
