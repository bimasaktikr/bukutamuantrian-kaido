<?php

namespace App\Filament\Resources\SubmethodResource\Pages;

use App\Filament\Resources\SubmethodResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListSubmethods extends ListRecords
{
    protected static string $resource = SubmethodResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
