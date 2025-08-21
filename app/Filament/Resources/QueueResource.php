<?php

namespace App\Filament\Resources;

use App\Filament\Resources\QueueResource\Pages;
use App\Filament\Resources\QueueResource\RelationManagers;
use App\Models\Queue;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\ActionsPosition;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Livewire\Component as Livewire;
use Illuminate\Contracts\View\View;
use Filament\Forms\Components\View as FilamentView;




class QueueResource extends Resource
{
    protected static ?string $model = Queue::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Transaksi';

    protected static ?string $navigationLabel = 'Daftar Antrian';

    protected static ?int $navigationSort = 2;


    public static function getNavigationBadge(): ?string
    {
        return static::getModel()::whereHas('transaction', function ($query) {
            $query->where('status', 'queue');
        })->count();
    }

    public static function getNavigationBadgeColor(): ?string
    {
        $count = static::getModel()::whereHas('transaction', function ($query) {
            $query->where('status', 'queue');
        })->count();

        return $count > 0 ? 'warning' : 'primary';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                //
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('transaction.customer.name'),
                TextColumn::make('transaction.customer.work.name')
                    ->label('Pekerjaan'),
                TextColumn::make('transaction.customer.institution.name'),
                TextColumn::make('transaction.service.name')
                    ->label('Nama Layanan'),
                TextColumn::make('transaction.date')
                    ->label('Tanggal'),
                    // ->date(), // Format the date column,
                TextColumn::make('number')
                    ->label('Nomor Antrian')
                    ->formatStateUsing(function ($state, $record) {
                        $prefix = $record->transaction?->service?->code ?? '';
                        $queueNumberFormatted = str_pad($state, 3, '0', STR_PAD_LEFT);
                        return "{$prefix}-{$queueNumberFormatted}";
                    })
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('transaction.status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'queue' => 'gray',
                        'onprocess' => 'warning',
                        'done' => 'success',
                    })
                    ->sortable()
                    ->searchable(),
            ])
            ->filters([
                Filter::make('status')
                        ->label('Filter by Status')
                        ->form([
                            Select::make('status')
                                ->options([
                                    'queue' => 'Queue',
                                    'onprocess' => 'On Process',
                                    'done' => 'Done',
                                ])
                                ->placeholder('All Statuses'),
                        ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $data['status']
                                ? $query->where('status', $data['status'])
                                : $query;
                        }),
                    Filter::make('transaction.date')
                        ->label('Filter by Date')
                        ->form([
                                DatePicker::make('date')->label('Tanggal'),
                            ])
                        ->query(function (Builder $query, array $data): Builder {
                            return $data['date']
                                ? $query->whereHas('transaction', function (Builder $query) use ($data) {
                                    $query->whereDate('date', $data['date']);
                                })
                                : $query;
                        }),
            ])
            ->actions([
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListQueues::route('/'),
            'create' => Pages\CreateQueue::route('/create'),
            'edit' => Pages\EditQueue::route('/{record}/edit'),
            'display' => Pages\QueueDisplay::route('/display'),
        ];
    }
}
