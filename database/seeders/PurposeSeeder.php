<?php

namespace Database\Seeders;

use App\Models\Purpose;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class PurposeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Purpose::create(['name' => 'Tugas Sekolah/Kuliah']);   //
        Purpose::create(['name' => 'Skripsi / Tesis / Disertasi']);   //
        Purpose::create(['name' => 'Penelitian']);   //
        Purpose::create(['name' => 'Perencanaan']);   //
        Purpose::create(['name' => 'Evaluasi']);   //
        Purpose::create(['name' => 'Diskusi']);   //

    }
}
