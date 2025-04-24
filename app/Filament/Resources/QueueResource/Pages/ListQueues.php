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

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
            Actions\Action::make('openDisplay')
                ->label('Open Operator Display')
                ->icon('heroicon-o-tv')
                ->url(fn () => static::$resource::getUrl('display'))
                ->openUrlInNewTab(),
            // Actions\Action::make('openPublicDisplay')
            //     ->label('Open Public Display')
            //     ->icon('heroicon-o-presentation-chart-bar')
            //     ->url(fn () => static::$resource::getUrl('public'))
            //     ->openUrlInNewTab(),
        ];
    }
    // protected static string $view = 'filament.resources.queue-resource.pages.list-queues';

    // protected function getTableActions(): array
    // {
    //     return [
    //         Action::make('print')
    //             ->label('Print')
    //             ->url(fn ($record) => route('queues.print', $record->id))
    //             ->icon('heroicon-o-printer')
    //             ->openUrlInNewTab()
    //             ->color('success'),
    //     ];
    // }
}

