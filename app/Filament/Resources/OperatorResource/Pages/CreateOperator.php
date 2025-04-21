<?php

namespace App\Filament\Resources\OperatorResource\Pages;

use App\Filament\Resources\OperatorResource;
use App\Models\User;
use Filament\Actions;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Support\Facades\Hash;

class CreateOperator extends CreateRecord
{
    protected static string $resource = OperatorResource::class;
    protected static bool $canCreateAnother = false;

    //customize redirect after create
    public function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Create the user first
        $user = User::create([
            'name' => $data['name'], // Set the same name as the operator
            'email' => $data['email'],
            'password' => Hash::make($data['password']),
        ]);

        // Assign user_id to the operator
        $data['user_id'] = $user->id;

        return $data;
    }
}
