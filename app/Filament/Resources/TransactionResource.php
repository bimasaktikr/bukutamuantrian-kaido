<?php

namespace App\Filament\Resources;

use App\Filament\Resources\TransactionResource\Pages;
use App\Filament\Resources\TransactionResource\RelationManagers;
use App\Models\Transaction;
use App\Filament\Guest\Pages\PublicFeedback;
use App\Services\TransactionService;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Infolist;
use Filament\Notifications\Notification;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Actions\Action;
use Filament\Tables\Columns\Layout\Split;
use Filament\Tables\Columns\Layout\Stack;
use Filament\Tables\Columns\SelectColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;


class TransactionResource extends Resource
{
    protected static ?string $model = Transaction::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';

    protected static ?string $navigationGroup = 'Operasional';

    protected static ?string $navigationLabel = 'Daftar Transaksi';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('customer_id')
                    ->label('nama')
                    ->relationship('customer', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('service_id')
                    ->label('namaLayanan')
                    ->relationship('service', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('submethod_id')
                    ->label('mediaLayanan')
                    ->relationship('submethod', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\Select::make('purpose_id')
                    ->label('tujuanLayanan')
                    ->relationship('purpose', 'name')
                    ->searchable()
                    ->required(),

                Forms\Components\DatePicker::make('date')
                    ->label('tanggal')
                    ->required(),

                Forms\Components\Select::make('status')
                    ->label('status')
                    ->options([
                        'queue' => 'queue',
                        'onprocess' => 'onprocess',
                        'done' => 'done',
                    ])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
        ->columns([
            Split::make([
                // LEFT: Identity stack (name, job, affiliation)
                Stack::make([
                    TextColumn::make('customer.name')
                        ->label('Customer')
                        ->weight('semibold')
                        ->sortable()
                        ->searchable(),

                    TextColumn::make('customer.work.name')
                        ->icon('heroicon-o-briefcase')
                        ->color('gray')
                        ->size('sm')
                        ->placeholder('-')
                        ->searchable(),

                    TextColumn::make('customer_affiliation')
                        ->state(function ($record) {
                            $institution = $record->customer->institution->name ?? null;
                            $university  = $record->customer->university->name ?? null;

                            return $institution
                                ? "Instansi {$institution}"
                                : ($university ? "Universitas {$university}" : '-');
                        })
                        ->color('gray')
                        ->size('xs')
                        ->searchable(query: function ($query, string $search) {
                            return $query
                                ->orWhereHas('customer.institution', fn ($q) => $q->where('name', 'like', "%{$search}%"))
                                ->orWhereHas('customer.university',  fn ($q) => $q->where('name', 'like', "%{$search}%"));
                        }),
                ])->space(1)->grow(true),

                // MIDDLE: Transaction details stack (date, service)
                Stack::make([
                    TextColumn::make('date')
                        ->label('Tanggal')
                        ->sortable()
                        ->searchable(),

                    TextColumn::make('service.name')
                        ->label('Nama Layanan')
                        ->placeholder('-'),
                ])->space(1)->grow(),

                // RIGHT: Status & feedback stack (badges + copyable link)
                Stack::make([
                    TextColumn::make('status')
                        ->label('Status')
                        ->badge()
                        ->color(fn ($state) => match ($state) {
                            'queue'     => 'gray',
                            'onprocess' => 'warning',
                            'done'      => 'success',
                            default     => 'gray',
                        }),
                        // ->visible(fn ($record) => $record?->queue === null),
                    TextColumn::make('employee.name')
                        ->label('Handled by')
                        ->badge()
                        ->color(fn ($state) => $state ? 'success' : 'warning')
                        ->visible(fn ($record) => $record?->employee !== null),

                    TextColumn::make('feedback.submited')
                        ->label('Feedback')
                        ->badge()
                        ->formatStateUsing(fn ($state) => $state ? 'Submitted' : 'Pending')
                        ->color(fn ($state) => $state ? 'success' : 'warning'),
                ])->space(1)->alignment('right'),
            ])->from('md'), // split into columns on md+ screens, stacks vertically on small
        ])
            ->filters([
                //
            ])
            ->defaultSort('date', 'desc') // newest first
            ->actions([

                // Action::make('UpdateStatus')
                //     ->label('')
                //     ->form([
                //         Forms\Components\Select::make('status')
                //             ->label('Status')
                //             ->options([
                //                 'queue' => 'Queue',
                //                 'onprocess' => 'On Process',
                //                 'done' => 'Done',
                //             ])
                //             ->required()
                //             ->reactive(), // 👈 important so Filament can react to changes

                //         Forms\Components\Select::make('employee_id')
                //             ->label('Handled by')
                //             ->relationship('employee', 'name')
                //             ->searchable()
                //             ->preload()
                //             ->visible(fn (callable $get) => $get('status') === 'onprocess') // 👈 conditional visibility
                //             ->required(fn (callable $get) => $get('status') === 'onprocess'),
                //     ])
                //     ->action(function (Transaction $record, array $data) {
                //         app(\App\Services\TransactionService::class)->updateStatus($record, $data['status']);

                //         // If status is onprocess, also update employee
                //         if ($data['status'] === 'onprocess' && isset($data['employee_id'])) {
                //             $record->update(['employee_id' => $data['employee_id']]);
                //         }

                //         \Filament\Notifications\Notification::make()
                //             ->title('Status updated')
                //             ->success()
                //             ->send();
                //     })
                //     ->modalHeading('Update Transaction Status')
                //     ->modalButton('Save')
                //     ->color('primary')
                //     ->icon('heroicon-m-adjustments-horizontal'),
                Action::make('UpdateStatus')
                    ->label('')
                    ->form([
                        // Show the last/current state (read-only)
                        Forms\Components\Placeholder::make('current_status')
                            ->label('Current Status')
                            ->content(fn (Transaction $record) => match ($record->status) {
                                'queue'     => 'Queue',
                                'onprocess' => 'On Process',
                                'done'      => 'Done',
                                default     => ucfirst($record->status ?? '—'),
                            }),

                        Forms\Components\Select::make('status')
                            ->label('New Status')
                            ->reactive()
                            // Options depend on current record status
                            ->options(fn (Transaction $record) => $record->status === 'onprocess'
                                ? ['done' => 'Done'] // rule #2
                                : [
                                    'queue'     => 'Queue',
                                    'onprocess' => 'On Process',
                                    'done'      => 'Done',
                                ])
                            // Default preselect for convenience
                            ->default(fn (Transaction $record) => $record->status === 'onprocess' ? 'done' : null)
                            ->required()
                            // Hard guard: if already onprocess, never allow queue (rule #1)
                            ->rule(function (Transaction $record) {
                                return function (string $attribute, $value, \Closure $fail) use ($record) {
                                    if ($record->status === 'onprocess' && $value === 'queue') {
                                        $fail('Once a transaction is On Process, it cannot be moved back to Queue.');
                                    }
                                };
                            }),

                        // Only show employee selector when moving to "onprocess"
                        Forms\Components\Select::make('employee_id')
                            ->label('Handled by')
                            ->relationship('employee', 'name')
                            ->searchable()
                            ->preload()
                            ->visible(fn (callable $get) => $get('status') === 'onprocess')
                            ->required(fn (callable $get) => $get('status') === 'onprocess'),
                    ])
                    ->action(function (Transaction $record, array $data) {
                        $from = $record->status;        // last (current) state
                        $to   = $data['status'];        // requested new state

                        // Server-side enforcement (double-check)
                        if ($from === 'onprocess' && $to === 'queue') {
                            throw ValidationException::withMessages([
                                'status' => 'Once a transaction is On Process, it cannot be moved back to Queue.',
                            ]);
                        }
                        if ($from === 'onprocess' && $to !== 'done') {
                            throw ValidationException::withMessages([
                                'status' => 'When already On Process, the only allowed transition is to Done.',
                            ]);
                        }

                        // Save employee only when moving to "onprocess"
                        if ($to === 'onprocess') {
                            $record->employee_id = $data['employee_id'] ?? $record->employee_id;
                        }

                        // Update status via your service
                        app(\App\Services\TransactionService::class)->updateStatus($record, $to);

                        // Rule #3: Always show last state → new state in the notification
                        \Filament\Notifications\Notification::make()
                            ->title('Status updated')
                            ->body(sprintf('Status: %s → %s',
                                match ($from) {
                                    'queue'     => 'Queue',
                                    'onprocess' => 'On Process',
                                    'done'      => 'Done',
                                    default     => ucfirst($from ?? '—'),
                                },
                                match ($to) {
                                    'queue'     => 'Queue',
                                    'onprocess' => 'On Process',
                                    'done'      => 'Done',
                                    default     => ucfirst($to ?? '—'),
                                },
                            ))
                            ->success()
                            ->send();
                    })
                    ->modalHeading('Update Transaction Status')
                    ->modalButton('Save')
                    ->color('primary')
                    ->icon('heroicon-m-adjustments-horizontal'),
                Action::make('viewFeedback')
                    ->label('Feedback')
                    ->icon('heroicon-o-eye')
                    ->visible(fn ($record) => filled($record->feedback))
                    ->infolist(function ($record): Infolist {
                        return Infolist::make()
                            ->state(['feedback' => $record->feedback])
                            ->schema([
                                TextEntry::make('feedback.uuid')->label('UUID'),
                                TextEntry::make('feedback.transaction.id')->label('Trans ID'),
                                TextEntry::make('feedback.rate')->label('Rate'),
                                TextEntry::make('feedback.comment')->label('Comment')->columnSpanFull(),
                                TextEntry::make('feedback.submited')->label('Submitted')
                                    ->formatStateUsing(fn ($state) => $state ? 'Yes' : 'No'),
                            ]);
                    })
                    ->modalSubmitAction(false), // purely view
                // Action::make('copyFeedbackLink')
                //     ->label('Copy Link')
                //     ->icon('heroicon-o-link')
                //     ->visible(fn ($record) => filled($record->feedback))
                //     ->action(fn ($record) => \Filament\Notifications\Notification::make()
                //         ->title('Link copied')
                //         ->body(route('public.feedback.show', $record->feedback->uuid))
                //         ->success()
                //         ->send()),
                Action::make('sendFeedbackLink')
                    ->label('Send Feedback Link')
                    ->icon('heroicon-o-paper-airplane')
                    ->requiresConfirmation()
                    ->visible(fn ($record) => filled($record->feedback))
                    ->action(function ($record) {
                        // $url = route('filament.guest.feedback.public', $record->feedback->uuid);
                        // $url = route('filament.guest.feedback.public', ['uuid' => $record->feedback->uuid]);
                        $url = PublicFeedback::getUrl(['uuid' => $record->feedback->uuid], panel: 'guest');


                        app(\App\Services\WhatsappService::class)->sendMessage([
                            'number'  => $record->customer->phone,
                            'message' => app(\App\Services\TransactionService::class)->buildFeedbackRequestMessage($record, $url),
                        ]);
                        Notification::make()
                            ->title('Link sent via WhatsApp')
                            ->success()
                            ->send();
                    }),
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
