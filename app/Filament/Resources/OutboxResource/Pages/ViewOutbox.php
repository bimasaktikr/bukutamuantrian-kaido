<?php

namespace App\Filament\Resources\OutboxResource\Pages;

use App\Filament\Resources\OutboxResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewOutbox extends ViewRecord
{
    protected static string $resource = OutboxResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
