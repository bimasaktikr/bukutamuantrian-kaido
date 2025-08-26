<?php

namespace App\Filament\Resources;

use App\Filament\Resources\OutboxResource\Pages;
use App\Filament\Resources\OutboxResource\RelationManagers;
use App\Models\Outbox;
use Filament\Tables\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OutboxResource extends Resource
{
    protected static ?string $model = Outbox::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationLabel = "Outbox";

    protected static ?string $navigationGroup = "Whatsapp";


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
                Tables\Columns\TextColumn::make('to')
                    ->label('to')
                    ->searchable()
                    ->sortable(),
                Tables\Columns\TextColumn::make('message')
                    ->label('message')
                    ->limit(50)
                    ->wrap()
                    ->searchable(),
                Tables\Columns\TextColumn::make('status')
                    ->label('status')
                    ->badge()
                    ->sortable(),
                Tables\Columns\TextColumn::make('response_code')
                    ->label('responseCode')
                    ->sortable(),
                Tables\Columns\TextColumn::make('sent_at')
                    ->label('sentAt')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\Action::make('resendWhatsapp')
                    ->label('Resend')
                    ->icon('heroicon-o-chat-bubble-left-ellipsis')
                    ->visible(fn ($record) => $record->status == 'failed')
                    ->action(function ($record) {
                        $outbox = $record->outbox;
                        try {
                            $resp = app(\App\Services\WhatsappService::class)->sendMessage([
                                'number'  => $outbox->to,
                                'message' => $outbox->message,
                            ]);
                            $outbox->update([
                                'status'        => 'sent',
                                'response_code' => 200,
                                'response_body' => is_array($resp) ? json_encode($resp) : (string) $resp,
                                'sent_at'       => now(),
                                'error'         => null,
                            ]);
                            \Filament\Notifications\Notification::make()->title('Sent')->success()->send();
                        } catch (\Throwable $e) {
                            $outbox->update([
                                'status' => 'failed',
                                'error'  => $e->getMessage(),
                            ]);
                            \Filament\Notifications\Notification::make()->title('Failed: '.$e->getMessage())->danger()->send();
                        }
                    }),
                ViewAction::make(),
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

    public static function infolist(\Filament\Infolists\Infolist $infolist): \Filament\Infolists\Infolist
    {
        return $infolist
            ->schema([
                \Filament\Infolists\Components\TextEntry::make('to')->label('to'),
                \Filament\Infolists\Components\TextEntry::make('message')->label('message')->limit(100),
                \Filament\Infolists\Components\TextEntry::make('status')->label('status'),
                \Filament\Infolists\Components\TextEntry::make('response_code')->label('responseCode'),
                \Filament\Infolists\Components\TextEntry::make('response_body')->label('responseBody')->limit(100),
                \Filament\Infolists\Components\TextEntry::make('sent_at')->label('sentAt'),
                \Filament\Infolists\Components\TextEntry::make('error')->label('error'),
                \Filament\Infolists\Components\TextEntry::make('related_type')->label('relatedType'),
                \Filament\Infolists\Components\TextEntry::make('related_id')->label('relatedId'),
                \Filament\Infolists\Components\TextEntry::make('created_at')->label('createdAt'),
                \Filament\Infolists\Components\TextEntry::make('updated_at')->label('updatedAt'),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOutboxes::route('/'),
            'create' => Pages\CreateOutbox::route('/create'),
            // 'view' => Pages\ViewOutbox::route('/{record}'),
            'edit' => Pages\EditOutbox::route('/{record}/edit'),
        ];
    }
}
