<?php

namespace Database\Seeders;

use App\Models\Submethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Submethod::create([
            'name' => 'Whatsapp',
            'method_id' => 1,
        ]);
        Submethod::create([
            'name' => 'Email',
            'method_id' => 1,
        ]);
        Submethod::create([
            'name' => 'Instagram/Facebook',
            'method_id' => 1,
        ]);
        Submethod::create([
            'name' => 'Datang Langsung',
            'method_id' => 2,
        ]);
    }
}
