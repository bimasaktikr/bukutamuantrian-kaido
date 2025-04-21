<?php

namespace App\Filament\Resources\QueueResource\Pages;

use App\Filament\Resources\QueueResource;
use Filament\Actions;
use Filament\Actions\Action;
use Filament\Resources\Pages\ListRecords;
use Filament\Tables\Concerns\InteractsWithTable;

class ListQueues extends ListRecords
{

    protected static string $resource = QueueResource::class;

    // protected static string $view = 'filament.resources.queue-resource.pages.list-queues';

    protected function getTableActions(): array
    {
        return [
            Action::make('print')
                ->label('Print')
                ->url(fn ($record) => route('queues.print', $record->id))
                ->icon('heroicon-o-printer')
                ->openUrlInNewTab()
                ->color('success'),
        ];
    }
}

