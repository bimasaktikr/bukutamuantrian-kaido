<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;

class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Main';

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
                TextColumn::make('customer.name')
                    ->label('Nama')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('date')
                    ->label('Tanggal')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.work.name')
                    ->label('Pekerjaan')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.work') // atau kolom lain yg kamu pakai
                    ->label('Institusi / Universitas')
                    ->formatStateUsing(function ($state, $record) {
                        $institution = $record->customer->institution->name ?? null;
                        $university = $record->customer->university->name ?? null;

                        return $institution
                            ? "Instansi $institution"
                            : ($university ? "Universitas $university" : '-');
                    })
                    ->sortable()
                    ->searchable(),
                TextColumn::make('service.name')
                    ->label('Nama Layanan'),
                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($state) => match ($state) {
                        'queue' => 'gray',
                        'onprocess' => 'warning',
                        'done' => 'success',
                    })
                    ->visible(function ($record) {
                        return $record?->queue === null;
                    })

                // SelectColumn::make('status')
                //     ->label('Status')
                //     ->options([
                //         'queue' => 'Queue',
                //         'onprocess' => 'On Process',
                //         'done' => 'Done',
                //     ])
                //     ->sortable()
                //     ->searchable()
                //     ->disabled(fn ($record) => $record->queue ? true : false) // Disable if a queue exists
                //     ->afterStateUpdated(function ($record, $state) {
                //         // Prevent status from going backwards (Queue → On Process → Done)

                //         // Case: Moving from 'Queue' to 'On Process' or 'Done' is allowed
                //         if ($record->status === 'queue' && $state === 'onprocess') {
                //             // No issue with this transition
                //             Log::info("Status antrian {$record->id} diubah menjadi 'On Process'");
                //             return;
                //         }

                //         // Case: Moving from 'On Process' to 'Done' is allowed
                //         if ($record->status === 'onprocess' && $state === 'done') {
                //             // No issue with this transition
                //             Log::info("Status antrian {$record->id} diubah menjadi 'Done'");
                //             return;
                //         }

                //         // Prevent status from going backward
                //         if ($record->status === 'onprocess' && $state === 'queue') {
                //             session()->flash('error', 'Cannot revert to Queue while On Process');
                //             Log::error("Attempted invalid status change from 'On Process' to 'Queue' for record {$record->id}");
                //             return; // Prevent the state change
                //         }

                //         if ($record->status === 'done' && ($state === 'queue' || $state === 'onprocess')) {
                //             session()->flash('error', 'Cannot revert to previous statuses from Done');
                //             Log::error("Attempted invalid status change from 'Done' to 'Queue' or 'On Process' for record {$record->id}");
                //             return; // Prevent the state change
                //         }

                //         // Optional: Log successful status updates
                //         Log::info("Status antrian {$record->id} diubah menjadi {$state}");
                //     }),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),

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
            'index' => Pages\ListTransactions::route('/'),
            'create' => Pages\CreateTransaction::route('/create'),
            'edit' => Pages\EditTransaction::route('/{record}/edit'),
        ];
    }
}
