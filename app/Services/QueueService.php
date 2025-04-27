<?php

namespace App\Services;

use App\Models\Queue;
use App\Models\Service;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use WPPConnectTeam\Wppconnect\Facades\Wppconnect;

class QueueService
{
    public $whatsappService;


    public function __construct(WhatsappService $whatsappService)
    {
        $this->whatsappService = $whatsappService;
    }

    public function getTodayQueues(): ?Collection
    {
        // This method retrieves all queues for today, including customer and service details
        // It returns null if no queues are found or if an error occurs
        try {
            $today = Carbon::today();

            $queues = Queue::with(['transaction.customer', 'transaction.service'])
                ->whereDate('created_at', $today)
                ->orderBy('number')
                ->get();

            if ($queues->isEmpty()) {
                Log::warning('Tidak ada data antrian untuk hari ini.');
                return null;
            }

            return $queues;
        } catch (\Throwable $e) {
            Log::error('Gagal mengambil data antrian: ' . $e->getMessage());
            return null;
        }
    }

    public function getLastQueue()
    {
        // Retrieve the service and its prefix code
        // $service = Service::find($service_id);
        // $prefix = $service->code ?? ''; // Use the code column for prefix, default to empty string if null

        // Get today's date
        $today = Carbon::today();

        // Get the last queue for today, specific to this service, ordered by number descending
        $lastQueue = Queue::whereHas('transaction', function ($query) use ($today) {
                                    $query->whereDate('date', $today);
                                })
                                ->orderBy('number', 'desc')
                                ->first();

        // Determine the next queue number
        // $nextQueueNumber = $lastQueue ? $lastQueue->number + 1 : 1;
        return $lastQueue ? $lastQueue->number + 1 : 1;// returns the last queue of today

        // Return the prefixed queue number
        // return $prefix . $nextQueueNumber;
    }

    // Method to handle queue creation if needed
    public function createQueue($transaction)
    {
        try {
            // Get the last queue number or default to 1
            $queueNumber = $this->getLastQueue() ?? 1;

            // Validate that the queue number is not null
            if (empty($queueNumber)) {
                throw new \Exception('Queue number generation failed.');
            }

            // Create the queue
            $queue = Queue::create([
                // 'date' => Carbon::today(),
                'number' => $queueNumber,
                'transaction_id' => $transaction->id,
            ]);

            // $this->sendQueueMessage($queue);

        } catch (\Exception $e) {
            // Rollback the transaction if queue creation fails
            DB::rollBack();

            // Log error and notify user
            Log::error('Error creating queue: ' . $e->getMessage());

            throw $e;  // Optionally rethrow the exception
        }

        try {
            $this->sendQueueMessage($queue);
        } catch (\Exception $e) {
            // Log the error
            Log::error('Error Sending Message for Queue: ' . $e->getMessage());
            throw $e;
        }
    }

    protected function sendQueueMessage($queue)
    {
        $queueDate = now()->format('d M Y');
        $customerName = $queue -> transaction->customer->name;
        $serviceName = $queue -> transaction->service->name;
        $queueNumberFormatted = str_pad($queue->number, 3, '0', STR_PAD_LEFT);
        $prefix = Service::find($queue->transaction->service_id)->code ?? '';
        $layananChoosed = $queue->transaction->layanan_choosed; // Assuming `layanan_choosed` is available

        // Send WhatsApp message
        $this->whatsappService->sendMessage([
            'number' => $queue->transaction->customer->phone, // Send to the customer's phone
            'message' => "Halo, Sahabat Data!\n\n" .
                "Terima kasih telah menggunakan layanan kami, berikut adalah detail antrian Anda:\n" .
                "Nama: {$customerName}\n" .
                "Nomor Antrian: {$prefix}-{$queueNumberFormatted}\n" .
                "Layanan yang Dibutuhkan: {$serviceName}\n" .
                "Media Layanan yang digunakan: {$layananChoosed}\n" .
                "Tanggal pelayanan: {$queueDate}\n\n" .
                "Tunjukkan pesan ini kepada petugas pelayanan saat anda datang ke PST BPS Kota Malang.",
        ]);
    }

}

