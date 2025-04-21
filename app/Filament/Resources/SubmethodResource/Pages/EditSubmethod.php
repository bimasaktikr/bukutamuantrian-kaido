<?php

namespace App\Filament\Resources\SubmethodResource\Pages;

use App\Filament\Resources\SubmethodResource;
use Filament\Actions;
use Filament\Resources\Pages\EditRecord;

class EditSubmethod extends EditRecord
{
    protected static string $resource = SubmethodResource::class;

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
