<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class CallQueuePage extends Page
{

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.pages.call-queue-page';

    protected static ?string $slug = 'call-queue';
}
