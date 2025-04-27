<?php

namespace App\Services;

use App\Models\Method;
use App\Models\Transaction;
use App\Models\Queue;
use App\Models\Service;
use App\Models\Submethod;
use App\Services\QueueService;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Filament\Notifications\Notification;
use PhpParser\Node\Stmt\TryCatch;

class TransactionService
{
    protected $queueService;
    protected $whatsappService;

    public function __construct(WhatsappService $whatsappService, QueueService $queueService)
    {
        $this->whatsappService = $whatsappService;
        $this->queueService = $queueService;
    }

    // Method to handle transaction creation
    public function createTransaction($data)
    {
        DB::beginTransaction();

        try {
            // Create the transaction
            $transaction = Transaction::create([
                'customer_id' => $data['customer_id'],
                'service_id' => $data['service_id'],
                'purpose_id' => $data['purpose_id'],
                'submethod_id' => $data['submethod_id'],
                'date' => $data['date'],
            ]);

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Log the error
            Log::error('Error saving transaction: ' . $e->getMessage());

            throw $e;  // Optionally rethrow the exception if you want to handle it later
        }

        try {
            //code...
            $submethod = Submethod::find($data['submethod_id']);
            // Handle queue creation if needed
            if ($submethod->method_id == 2) {
                $this->queueService->createQueue($transaction);
            } else {
                $this->sendTransactionMessage($transaction);
            }
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error Sending Message for Transaction: ' . $e->getMessage());
            throw $e;
        }

        return $transaction;
    }

    protected function sendTransactionMessage($transaction)
    {
        $queueDate = now()->format('d M Y');
        $customerName = $transaction->customer->name;
        $serviceName = $transaction->service->name;
        // $queueNumberFormatted = str_pad($transaction->queue_number, 3, '0', STR_PAD_LEFT);
        // $prefix = Service::find($transaction->service_id)->code ?? '';
        $layananChoosed = $transaction->submethod->name; // Assuming `layanan_choosed` is available

        // Send WhatsApp message
        $this->whatsappService->sendMessage([
            'number' => $transaction->customer->phone, // Send to the customer's phone
            'message' => "Halo, Sahabat Data!\n\n" .
                "Terima kasih telah menggunakan layanan kami, berikut adalah transaksi Anda:\n" .
                "Nama: {$customerName}\n" .
                // "Nomor Antrian: {$prefix}-{$queueNumberFormatted}\n" .
                "Layanan yang Dibutuhkan: {$serviceName}\n" .
                "Media Layanan yang digunakan: {$layananChoosed}\n" .
                "Tanggal pelayanan: {$queueDate}\n\n" .
                "Terimakasih sudah menggunakan layanan PST Online BPS Kota Malang.",
        ]);
    }


}
