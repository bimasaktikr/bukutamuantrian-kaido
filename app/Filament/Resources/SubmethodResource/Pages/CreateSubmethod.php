<?php

namespace App\Filament\Resources\SubmethodResource\Pages;

use App\Filament\Resources\SubmethodResource;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;

class CreateSubmethod extends CreateRecord
{
    protected static string $resource = SubmethodResource::class;
    protected static bool $canCreateAnother = true;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function getCreatedNotificationTitle(): ?string
{
    return 'Submethod Created';
}
}
