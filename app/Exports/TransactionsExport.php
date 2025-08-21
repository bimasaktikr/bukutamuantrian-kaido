<?php

namespace App\Exports;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class TransactionsExport implements FromCollection, WithHeadings
{
    public function collection()
    {
        return Transaction::with(['customer', 'service', 'submethod', 'purpose'])
            ->select('id', 'customer_id', 'service_id', 'submethod_id', 'purpose_id', 'status', 'date')
            ->get()
            ->map(function ($transaction) {
                return [
                    'ID' => $transaction->id,
                    'Customer' => $transaction->customer->name ?? '',
                    'Service' => $transaction->service->name ?? '',
                    'Submethod' => $transaction->submethod->name ?? '',
                    'Purpose' => $transaction->purpose->name ?? '',
                    'Status' => $transaction->status,
                    // 'Date' => $transaction->date->format('d M Y'),
                    'Date'     => Carbon::parse($transaction->date)->format('d M Y'),

                ];
            });
    }

    public function headings(): array
    {
        return ['ID', 'Customer', 'Service', 'Submethod', 'Purpose', 'Status', 'Date'];
    }
}
