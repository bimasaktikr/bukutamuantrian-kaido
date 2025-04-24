<?php

namespace Database\Seeders;

use App\Models\SubMethod;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class SubMethodSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        SubMethod::create([
            'name' => 'Whatsapp',
            'method_id' => 1,
        ]);
        SubMethod::create([
            'name' => 'Email',
            'method_id' => 1,
        ]);
        SubMethod::create([
            'name' => 'Instagram/Facebook',
            'method_id' => 1,
        ]);
        SubMethod::create([
            'name' => 'Datang Langsung',
            'method_id' => 2,
        ]);
    }
}
