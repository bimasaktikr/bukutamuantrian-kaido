<?php

namespace App\Filament\Pages;

use App\Models\Whatsapp as ModelsWhatsapp;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Forms\Contracts\HasForms;
use Filament\Forms\Form;
use Filament\Pages\Page;
use Filament\Notifications\Notification;

class WhatsappPage extends Page implements HasForms
{
    use InteractsWithForms;

    protected static ?string $navigationIcon = 'heroicon-o-cog-6-tooth';

    protected static ?string $navigationGroup = 'Whatsapp';

    protected static ?string $slug = 'whatsapp-server';

    protected static string $view = 'filament.pages.whatsapp-page';

    public $key;
    public $session_name;
    public $server_host_url;

    public function mount()
    {
        $whatsapp = ModelsWhatsapp::latest()->first();
        if ($whatsapp) {
            $this->key = $whatsapp->key;
            $this->session_name = $whatsapp->session_name;
            $this->server_host_url = $whatsapp->server_host_url;
        }
    }

    public static function getNavigationLabel(): string
    {
        return 'Whatsapp Server';
    }

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('key')
                    ->label('apiKey')
                    ->required(),
                Forms\Components\TextInput::make('session_name')
                    ->label('sessionName')
                    ->required(),
                Forms\Components\TextInput::make('server_host_url')
                    ->label('serverHostUrl')
                    ->url()
                    ->nullable(),
            ])
            ->statePath('data'); // <-- bind to $this->data

    }

    public function save()
    {
        $state = $this->form->getState();

        $payload = [
            'key'             => $state['key'] ?? null,
            'session_name'    => $state['session_name'] ?? null,
            'server_host_url' => $state['server_host_url'] ?? null,
        ];

        $whatsapp = ModelsWhatsapp::latest()->first();
        $whatsapp ? $whatsapp->update($payload) : ModelsWhatsapp::create($payload);

        Notification::make()->title('whatsappSetting updated')->success()->send();
    }

}
