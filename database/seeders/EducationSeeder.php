<?php

namespace Database\Seeders;

use App\Models\Education;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class EducationSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Education::create(['name' => 'SD / Tidak memiliki Ijazah']);   //
        Education::create(['name' => 'SMP sederajat']);   //
        Education::create(['name' => 'SMA sederajat']);   //
        Education::create(['name' => 'SMK']);   //
        Education::create(['name' => 'D1/D2/D3']);   //
        Education::create(['name' => 'D4/S1']);   //
        Education::create(['name' => 'S2']);   //
        Education::create(['name' => 'S3']);   //

    }
}
