<?php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use Filament\Resources\Pages\Page;
use Illuminate\Contracts\View\View;

class QueueDisplay extends Page
{
    protected static string $resource = QueueResource::class;

    protected static string $view = 'filament.resources.queue-resource.pages.queue-display';

    protected static ?string $title = 'Queue Display';

    protected static ?string $navigationIcon = 'heroicon-o-tv';

    protected static ?string $navigationLabel = 'Operator Display';

    protected static ?int $navigationSort = 2;

    // public function getHeader(): ?View
    // {
    //     return [];
    // }

    public function getSubheading(): ?string
    {
        return 'Control and monitor the queue system';
    }
}
