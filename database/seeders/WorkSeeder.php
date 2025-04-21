<?php

namespace Database\Seeders;

use App\Models\Work;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class WorkSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Work::create([
            'name' => 'Mahasiswa',
        ]);
        Work::create([
            'name' => 'Aparatur Sipil Negara',
        ]);
        Work::create([
            'name' => 'Akademisi',
        ]);
        Work::create([
            'name' => 'Pengusaha',
        ]);
    }
}
