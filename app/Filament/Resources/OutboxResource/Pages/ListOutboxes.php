<?php

namespace App\Filament\Resources\OutboxResource\Pages;

use App\Filament\Resources\OutboxResource;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListOutboxes extends ListRecords
{
    protected static string $resource = OutboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}
