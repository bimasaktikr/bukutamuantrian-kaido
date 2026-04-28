<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;

class Logs extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = 'System';

    protected static ?string $navigationLabel = 'Logs';

    protected static ?int $navigationSort = 10;

    protected static string $view = 'filament.pages.logs';

    public static function canAccess(): bool
    {
        return auth()->user()?->hasRole('super_admin')
            || auth()->user()?->hasRole('admin');
    }
}