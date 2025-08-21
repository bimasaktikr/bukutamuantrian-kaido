<?php

namespace App\Filament\Pages;

use App\Models\Transaction;

use Filament\Pages\Page;

use Filament\Tables;
use Filament\Tables\Table;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Columns\Summarizers\Count;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\SelectFilter;

use Filament\Forms\Components\DatePicker;

use Illuminate\Database\Eloquent\Builder;

class TransactionReport extends Page implements HasTable
{
    use InteractsWithTable;

    protected static ?string $navigationIcon  = 'heroicon-o-document-chart-bar';
    protected static ?string $navigationGroup = 'Transaksi';
    protected static ?string $navigationLabel = 'Transaction Report';
    protected static ?string $slug            = 'report-transactions';
    protected static string  $view            = 'filament.pages.transaction-report';

    public static function canAccess(): bool
    {
        return auth()->check() && auth()->user()->hasAnyRole(['super_admin','ketua tim']);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query($this->baseQuery())
            ->columns([
                TextColumn::make('date')
                    ->label('Date')
                    ->date('Y-m-d')
                    ->sortable()
                    ->toggleable()
                    ->summarize(Count::make()->label('Rows')),

                TextColumn::make('customer.name')
                    ->label('Customer')
                    ->searchable()
                    ->toggleable(),

                TextColumn::make('service.name')
                    ->label('Service')
                    ->badge()
                    ->color('gray')
                    ->sortable()
                    ->searchable(),

                TextColumn::make('purpose.name')
                    ->label('Purpose')
                    ->toggleable(),

                TextColumn::make('submethod.name')
                    ->label('Channel')
                    ->toggleable(),

                TextColumn::make('status')
                    ->label('Status')
                    ->badge()
                    ->color(fn (string $state) => match ($state) {
                        'completed' => 'success',
                        'pending'   => 'warning',
                        'cancelled' => 'danger',
                        default     => 'gray',
                    })
                    ->sortable(),
            ])
            ->filters([
                // Date range
                Filter::make('date_range')
                    ->form([
                        DatePicker::make('from')->label('From'),
                        DatePicker::make('until')->label('Until'),
                    ])
                    ->query(function (Builder $q, array $data) {
                        return $q
                            ->when($data['from']  ?? null, fn ($qq, $d) => $qq->whereDate('date', '>=', $d))
                            ->when($data['until'] ?? null, fn ($qq, $d) => $qq->whereDate('date', '<=', $d));
                    })
                    ->indicateUsing(fn (array $data) => array_filter([
                        $data['from']  ? 'From: '  . $data['from']  : null,
                        $data['until'] ? 'Until: ' . $data['until'] : null,
                    ])),

                // Quick periods
                Filter::make('today')->label('Today')
                    ->query(fn (Builder $q) => $q->whereDate('date', today()))
                    ->toggle(), // acts like a checkbox

                Filter::make('this_month')->label('This Month')
                    ->query(fn (Builder $q) => $q->whereBetween('date', [now()->startOfMonth(), now()->endOfMonth()]))
                    ->toggle(),

                // By service/purpose/submethod/status
                SelectFilter::make('service_id')->label('Service')
                    ->relationship('service', 'name')
                    ->preload()->searchable(),

                SelectFilter::make('purpose_id')->label('Purpose')
                    ->relationship('purpose', 'name')
                    ->preload()->searchable(),

                SelectFilter::make('submethod_id')->label('Channel')
                    ->relationship('submethod', 'name')
                    ->preload()->searchable(),

                SelectFilter::make('status')->label('Status')
                    ->options([
                        'pending'   => 'Pending',
                        'completed' => 'Completed',
                        'cancelled' => 'Cancelled',
                    ]),
            ])
            ->defaultSort('date', 'desc')
            ->paginated([25, 50, 100])
            ->persistFiltersInSession()
            ->striped()
            ->headerActions([
                Action::make('export_excel')
                    ->label('Export to Excel')
                    ->icon('heroicon-o-arrow-down-tray')
                    ->visible()
                    ->action(function () {
                        $monthYear = now()->format('F_Y'); // Example: "August_2025"
                        $fileName = "Transactions_{$monthYear}.xlsx";

                        return \Maatwebsite\Excel\Facades\Excel::download(
                            new \App\Exports\TransactionsExport, // your export class
                            $fileName
                        );
                    })
            ]);
    }

    protected function baseQuery(): Builder
    {
        return Transaction::query()
            ->with([
                'customer:id,name',
                'service:id,name',
                'purpose:id,name',
                'submethod:id,name',
            ])
            ->select(['id','date','customer_id','service_id','purpose_id','submethod_id','status']);
    }
}
