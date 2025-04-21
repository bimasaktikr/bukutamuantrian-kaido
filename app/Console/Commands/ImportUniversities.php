<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Http;
use App\Models\University;

class ImportUniversities extends Command
{
    protected $signature = 'import:universities';
    protected $description = 'Import universities from external API';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $response = Http::get('http://universities.hipolabs.com/search?country=Indonesia');
        $universities = $response->json();

        foreach ($universities as $uni) {
            University::updateOrCreate(
                ['name' => $uni['name']],
            );
        }

        $this->info('Universities imported successfully.');
    }
}
