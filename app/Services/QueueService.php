<?php

namespace App\Services;

use App\Models\Outbox;
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

    public function createQueue($transaction): Queue
    {
        // 1) Persist queue + outbox atomically
        [$queue, $outbox] = DB::transaction(function () use ($transaction) {
            $queueNumber = $this->getLastQueue();

            $queue = Queue::create([
                'number'         => $queueNumber,
                'transaction_id' => $transaction->id,
            ]);

            // prepare WA message text
            $payload = $this->buildQueueMessage($queue);

            // create outbox entry in "pending"
            $outbox = Outbox::create([
                'to'           => $queue->transaction->customer->phone,
                'message'      => $payload,
                'status'       => 'pending',
                'related_type' => get_class($queue),
                'related_id'   => $queue->id,
            ]);

            return [$queue, $outbox];
        });

        // 2) Send WA in a separate try/catch (no DB rollback on failure)
        try {
            $response = $this->whatsappService->sendMessage([
                'number'  => $outbox->to,
                'message' => $outbox->message,
            ]);

            // normalize response
            $code = is_array($response) && isset($response['status']) ? (int) $response['status'] : 200;

            $outbox->update([
                'status'        => 'sent',
                'response_code' => $code,
                'response_body' => is_array($response) ? json_encode($response) : (string) $response,
                'sent_at'       => now(),
            ]);
        } catch (\Throwable $e) {
            Log::error('WA send failed for queue '.$queue->id.' : '.$e->getMessage());

            $outbox->update([
                'status'        => 'failed',
                'response_code' => 0,
                'error'         => $e->getMessage(),
            ]);
            // DO NOT throw; we want queue to remain saved
        }

        return $queue;
    }

    /** Build the WhatsApp message text for a queue. */
    protected function buildQueueMessage(Queue $queue): string
    {
        $queueDate           = now()->format('d M Y');
        $customerName        = $queue->transaction->customer->name;
        $serviceName         = $queue->transaction->service->name;
        $queueNumberFormatted= str_pad($queue->number, 3, '0', STR_PAD_LEFT);
        $prefix              = Service::find($queue->transaction->service_id)->code ?? '';
        $layananChoosed      = $queue->transaction->submethod->name ?? '';

        return "Halo, Sahabat Data!\n\n"
            ."Terima kasih telah menggunakan layanan kami, berikut adalah detail antrian Anda:\n"
            ."Nama: {$customerName}\n"
            ."Nomor Antrian: {$prefix}-{$queueNumberFormatted}\n"
            ."Layanan yang Dibutuhkan: {$serviceName}\n"
            ."Media Layanan yang digunakan: {$layananChoosed}\n"
            ."Tanggal pelayanan: {$queueDate}\n\n"
            ."Tunjukkan pesan ini kepada petugas pelayanan saat anda datang ke PST BPS Kota Malang.";
    }

    // protected function sendQueueMessage($queue)
    // {
    //     $queueDate = now()->format('d M Y');
    //     $customerName = $queue -> transaction->customer->name;
    //     $serviceName = $queue -> transaction->service->name;
    //     $queueNumberFormatted = str_pad($queue->number, 3, '0', STR_PAD_LEFT);
    //     $prefix = Service::find($queue->transaction->service_id)->code ?? '';
    //     $layananChoosed = $queue->transaction->layanan_choosed; // Assuming `layanan_choosed` is available

    //     // Send WhatsApp message
    //     $this->whatsappService->sendMessage([
    //         'number' => $queue->transaction->customer->phone, // Send to the customer's phone
    //         'message' => "Halo, Sahabat Data!\n\n" .
    //             "Terima kasih telah menggunakan layanan kami, berikut adalah detail antrian Anda:\n" .
    //             "Nama: {$customerName}\n" .
    //             "Nomor Antrian: {$prefix}-{$queueNumberFormatted}\n" .
    //             "Layanan yang Dibutuhkan: {$serviceName}\n" .
    //             "Media Layanan yang digunakan: {$layananChoosed}\n" .
    //             "Tanggal pelayanan: {$queueDate}\n\n" .
    //             "Tunjukkan pesan ini kepada petugas pelayanan saat anda datang ke PST BPS Kota Malang.",
    //     ]);
    // }

}

