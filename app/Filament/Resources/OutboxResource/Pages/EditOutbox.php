<?php

namespace App\Filament\Resources\OutboxResource\Pages;

use App\Filament\Resources\OutboxResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditOutbox extends EditRecord
{
    protected static string $resource = OutboxResource::class;

    protected function getHeaderActions(): array
    {
        return [
        ];
    }

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
