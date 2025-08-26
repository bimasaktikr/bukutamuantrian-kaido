<?php

namespace App\Services;

use App\Models\Feedback;
use App\Models\Method;
use App\Models\Outbox;
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

use App\Services\FeedbackService;


class TransactionService
{
    protected $queueService;
    protected $whatsappService;
    protected $feedbackService;


    public function __construct(WhatsappService $whatsappService, QueueService $queueService, FeedbackService $feedbackService)
    {
        $this->whatsappService = $whatsappService;
        $this->queueService = $queueService;
        $this->feedbackService = $feedbackService;
    }

    // Method to handle transaction creation
    public function createTransaction($data)
    {
        $outbox = null;    // will hold outbox for non-queue messages
        $transaction = null;
        $submethod = null;

        DB::beginTransaction();

        try {
            // Create the transaction
            $transaction = Transaction::create([
                'customer_id' => $data['customer_id'],
                'service_id' => $data['service_id'],
                'purpose_id' => $data['purpose_id'],
                'submethod_id' => $data['submethod_id'],
                'category' => $data['category'],
                'date' => $data['date'],
            ]);

            $submethod = Submethod::find($data['submethod_id']);

            if ((int)$submethod->method_id === 2) {
                // Queue flow — your QueueService already handles its own outbox
                $this->queueService->createQueue($transaction);
            } else {
                // Non-queue flow — create outbox "pending" here (send later)
                $payload = $this->buildTransactionMessage($transaction);

                $outbox = Outbox::create([
                    'to'           => $transaction->customer->phone,
                    'message'      => $payload,
                    'status'       => 'pending',
                    'related_type' => get_class($transaction),
                    'related_id'   => $transaction->id,
                ]);
            }

            // Commit the transaction
            DB::commit();
        } catch (\Exception $e) {
            // Rollback transaction on error
            DB::rollBack();

            // Log the error
            Log::error('Error saving transaction: ' . $e->getMessage());

            throw $e;  // Optionally rethrow the exception if you want to handle it later
        }


        // ---- After commit: try sending WA for non-queue flow ----
        if ($outbox) {
            try {
                $resp = $this->whatsappService->sendMessage([
                    'number'  => $outbox->to,
                    'message' => $outbox->message,
                ]);

                $code = is_array($resp) && isset($resp['status']) ? (int)$resp['status'] : 200;

                $outbox->update([
                    'status'        => 'sent',
                    'response_code' => $code,
                    'response_body' => is_array($resp) ? json_encode($resp) : (string)$resp,
                    'sent_at'       => now(),
                ]);
            } catch (\Throwable $e) {
                Log::error('WA send failed (transaction '.$transaction->id.'): '.$e->getMessage());

                $outbox->update([
                    'status'        => 'failed',
                    'response_code' => 0,
                    'error'         => $e->getMessage(),
                ]);
                // Don’t throw — we want transaction to remain saved
            }
        }

        return $transaction;
    }

    protected function buildTransactionMessage(Transaction $transaction): string
    {
        $date          = now()->format('d M Y');
        $customerName  = $transaction->customer->name ?? '';
        $serviceName   = $transaction->service->name ?? '';
        $layanan       = $transaction->submethod->name ?? '';

        return "Halo, Sahabat Data!\n\n"
            ."Terima kasih telah menggunakan layanan kami, berikut adalah transaksi Anda:\n"
            ."Nama: {$customerName}\n"
            ."Layanan yang Dibutuhkan: {$serviceName}\n"
            ."Media Layanan yang digunakan: {$layanan}\n"
            ."Tanggal pelayanan: {$date}\n\n"
            ."Terimakasih sudah menggunakan layanan PST Online BPS Kota Malang.";
    }

    public function updateStatus(Transaction $tx, string $newStatus): void
    {
        $old = (string) $tx->status;
        $tx->update(['status' => $newStatus]);
        Log::info("Transaction {$tx->id} status: {$old} -> {$newStatus}");

        // Only when entering done/completed from a non-done state
        $isNowDone = in_array(strtolower($newStatus), ['done', 'completed'], true);
        $wasDone   = in_array(strtolower($old),       ['done', 'completed'], true);
        if (! $isNowDone || $wasDone) {
            return;
        }

        // 1) Ensure feedback exists (idempotent)
        $feedback = $this->feedbackService->createForTransaction($tx);

        // 2) Skip if we already queued/sent one
        $alreadyQueued = Outbox::query()
            ->where('related_type', Feedback::class)
            ->where('related_id', $feedback->id)
            ->whereIn('status', ['queued', 'sent', 'pending'])
            ->exists();

        if ($alreadyQueued) {
            return;
        }

        // 3) Build message + link
        $url     = $this->feedbackService->publicUrl($feedback);
        $message = $this->buildFeedbackRequestMessage($tx, $url);
        $to      = $tx->customer->phone ?? null;

        if (blank($to)) {
            Log::warning("No phone number for transaction {$tx->id}, skipping WA invite.");
            return;
        }

        // 4) Create Outbox as 'pending'
        $outbox = Outbox::create([
            'to'            => $to,
            'message'       => $message,
            'status'        => 'pending',
            'related_type'  => Feedback::class,
            'related_id'    => $feedback->id,
            'response_code' => null,
            'response_body' => null,
            'error'         => null,
        ]);

        // 5) After commit: try sending WA (non-queue flow)
        DB::afterCommit(function () use ($outbox, $tx) {
            try {
                // Resolve the service here to avoid capturing $this in the closure
                $wa   = app(\App\Services\WhatsappService::class);
                $resp = $wa->sendMessage([
                    'number'  => $outbox->to,
                    'message' => $outbox->message,
                ]);

                $code = (is_array($resp) && isset($resp['status'])) ? (int) $resp['status'] : 200;

                $outbox->update([
                    'status'        => 'sent',
                    'response_code' => $code,
                    'response_body' => is_array($resp) ? json_encode($resp) : (string) $resp,
                    'sent_at'       => now(),
                    'error'         => null,
                ]);
            } catch (\Throwable $e) {
                Log::error('WA send failed (transaction '.$tx->id.'): '.$e->getMessage());

                $outbox->update([
                    'status'        => 'failed',
                    'response_code' => 0,
                    'error'         => $e->getMessage(),
                ]);
                // Don’t throw — keep transaction status change
            }
        });

        // If you're not inside a DB transaction, the afterCommit callback will run immediately.
    }


    /** WhatsApp text when asking for feedback after completion */
    protected function buildFeedbackRequestMessage(Transaction $tx, string $url): string
    {
        $name    = $tx->customer->name ?? 'Pelanggan';
        $service = $tx->service->name ?? '';
        $date    = now()->format('d M Y');

        return "Halo, Sahabat Data {$name}!\n\n"
            ."Layanan *{$service}* Anda pada {$date} telah *SELESAI*.\n"
            ."Mohon bantu kami dengan memberikan penilaian melalui tautan berikut:\n{$url}\n\n"
            ."Terima kasih atas partisipasinya 🙏"
            ."\n"
            ."Note: Mohon simpan nomor layanan BPS Kota Malang ini agar lebih mudah membuka tautan";
    }

}
