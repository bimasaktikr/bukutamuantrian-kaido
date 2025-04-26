<?php

namespace App\Filament\Pages;

use App\Models\Whatsapp as ModelsWhatsapp;
use App\Settings\Whatsapp;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Pages\SettingsPage;

class WhatsappSetting extends SettingsPage
{
    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static string $settings = ModelsWhatsapp::class;

    protected static ?string $navigationGroup = 'Settings';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('API Key')
                    ->required(),
                Forms\Components\TextInput::make('session_name')
                    ->label('Session Name')->required(),
                    // Forms\Components\TextInput::make('token')
                    //     ->label('Token')
                    //     ->nullable(),
                Forms\Components\TextInput::make('server_host_url')
                    ->label('Server Host URL')
                    ->url()
                    ->nullable(),
                // Forms\Components\Toggle::make('status')
                //     ->label('Active Status')
                //     ->disabled()
                //     ->required(),
            ]);
    }
}
