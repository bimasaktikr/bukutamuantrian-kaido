<?php

namespace App\Filament\Resources;

use App\Filament\Resources\FeedbackResource\Pages;
use App\Filament\Resources\FeedbackResource\RelationManagers;
use App\Models\Feedback;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class FeedbackResource extends Resource
{
    protected static ?string $model = Feedback::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Feedback';

    protected static ?int $navigationSort = 3;

    public static function form(Form $form): Form
    {
        return $form->schema([
            Forms\Components\TextInput::make('uuid')->disabled()->dehydrated(false),
            Forms\Components\Select::make('transaction_id')
                ->relationship('transaction', 'id')->searchable()->required(),
            Forms\Components\TextInput::make('rate')->numeric()->minValue(1)->maxValue(5),
            Forms\Components\Textarea::make('comment')->rows(4),
            Forms\Components\Toggle::make('submited')->label('Submitted'),
        ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            // ->defaultSort('transaction.date', 'desc')
            ->modifyQueryUsing(fn (Builder $query) =>
            $query->leftJoin('transactions', 'feedback.transaction_id', '=', 'transactions.id')
                ->select('feedback.*')
                ->orderByDesc('transactions.date')
            )
            ->columns([
                TextColumn::make('uuid')
                    ->label('UUID')
                    ->copyable(),
                // TextColumn::make('transaction.id')
                //     ->label('Trans ID')->sortable(),
                // TextColumn::make('transaction.customer.name')
                //     ->label('Customer')
                //     ->searchable()
                //     ->sortable()
                //     ,
                // TextColumn::make('transaction.date')
                //     ->label('Date')
                //     ->searchable()
                //     ->sortable(),
                TextColumn::make('transaction_detail')
                    ->label('Transaction')
                    ->html()
                    ->getStateUsing(function (Feedback $record) {
                        return "
                            <div class='space-y-1'>
                                <div class='text-sm font-bold text-primary-600'>
                                    #{$record->transaction->id}
                                </div>

                                <div class='text-sm text-gray-900'>
                                    {$record->transaction->customer->name}
                                </div>

                                <div class='text-sm text-gray-900'>
                                    {$record->transaction->service->name}
                                </div>

                                <div class='text-xs text-gray-500'>
                                    {$record->transaction->date}
                                </div>
                            </div>
                        ";
                    })
                    ->searchable(query: function ($query, $search) {
                        $query->whereHas('transaction.customer', fn ($q) =>
                            $q->where('name', 'like', "%{$search}%")
                        );
                    }),
                TextColumn::make('rate')->sortable(),
                TextColumn::make('submited')->label('Submitted')->badge()
                    ->color(fn ($state) => $state ? 'success' : 'gray')
                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                TextColumn::make('public_url')
                    ->label('Public URL')
                    ->getStateUsing(fn (Feedback $r) =>
                        route('filament.guest.pages.f.{uuid}', [
                            'uuid' => $r->uuid,
                        ])
                    )
                    ->url(fn (Feedback $r) =>
                        route('filament.guest.pages.f.{uuid}', [
                            'uuid' => $r->uuid,
                        ])
                    )
                    ->openUrlInNewTab()
                    ->copyable()
                    ->toggleable(),
            ])
            ->filters([])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
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
            'index' => Pages\ListFeedback::route('/'),
            'create' => Pages\CreateFeedback::route('/create'),
            'view' => Pages\ViewFeedback::route('/{record}'),
            'edit' => Pages\EditFeedback::route('/{record}/edit'),
        ];
    }
}
