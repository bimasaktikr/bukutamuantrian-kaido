<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class TeamSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('teams')->insert([
            [
                'id' => 1,
                'name' => 'Umum',
            ],
            [
                'id' => 2,
                'name' => 'Sosial',
            ],
            [
                'id' => 3,
                'name' => 'Produksi',
            ],
            [
                'id' => 4,
                'name' => 'Distribusi',
            ],
            [
                'id' => 5,
                'name' => 'Neraca',
            ],
            [
                'id' => 6,
                'name' => 'IPDS',
            ],
        ]);
    }
}
