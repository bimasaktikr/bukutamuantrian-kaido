<?php

namespace App\Livewire;

use App\Models\Queue;
use App\Models\Transaction;
use Filament\Forms;
use Filament\Forms\Concerns\InteractsWithForms;
use Filament\Notifications\Notification;
use Illuminate\Support\Carbon;
use Livewire\Component;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Events\QueueUpdated;


class QueueDisplay extends Component
{
    use InteractsWithForms;

    public $currentTransaction = null;
    public $nextInQueue = null;
    public $queueList = [];
    public $completedList = [];
    public $counterName = "Counter 1";

    protected $listeners = [
        'queueUpdated' => 'handleQueueUpdate',
        'refreshQueue' => '$refresh'
    ];

    public function mount()
    {
        $this->loadQueueData();
    }

    public function loadQueueData()
    {
        // Get the current transaction being processed
        $this->currentTransaction = Transaction::whereHas('queue')
            ->where('status', 'onprocess')
            ->where('date', Carbon::today())
            ->with(['queue', 'customer', 'service', 'purpose'])
            ->first();

        // Get the next transaction in queue
        $this->nextInQueue = Transaction::whereHas('queue')
            ->where('status', 'queue')
            ->where('date', Carbon::today())
            ->with(['queue', 'customer', 'service', 'purpose'])
            ->orderBy('created_at')
            ->first();

        // Get the waiting queue list
        $this->queueList = Transaction::whereHas('queue')
            ->where('status', 'queue')
            ->where('date', Carbon::today())
            ->with(['queue', 'customer', 'service', 'purpose'])
            ->orderBy('created_at')
            ->limit(10)
            ->get();

        // Get the completed transactions for today
        $this->completedList = Transaction::whereHas('queue')
            ->where('status', 'done')
            ->whereDate('date',Carbon::today())
            ->with(['queue', 'customer', 'service', 'purpose'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function callNext()
    {
        DB::beginTransaction();
        try {
            // Mark current transaction as done if exists
            if ($this->currentTransaction) {
                $this->currentTransaction->status = 'done';
                $this->currentTransaction->date = now();
                $this->currentTransaction->save();
            }

            // Get the next transaction in queue
            $nextTransaction = Transaction::whereHas('queue')
                ->where('status', 'queue')
                ->where('date', Carbon::today())
                ->orderBy('created_at')
                ->first();

            if ($nextTransaction) {
                $nextTransaction->status = 'onprocess';
                $nextTransaction->save();

                $customerName = $nextTransaction->customer->name;
                $queueNumber = $nextTransaction->queue->number;

                // Broadcast the event to refresh all connected clients
                $this->dispatch('queueUpdated', [
                    'queueNumber' => $queueNumber,
                    'customerName' => $customerName
                // ])->to('queue-channel');
                ])->to('queue-display');

                event(new QueueUpdated($queueNumber, $customerName));

                // Show notification
                Notification::make()
                    ->title("Called Queue #$queueNumber - $customerName")
                    ->success()
                    ->send();

                // Emit an event for the speech synthesis
                $this->dispatch('speakQueue', [
                    'queueNumber' => $queueNumber,
                    'customerName' => $customerName,
                    'counterName' => $this->counterName
                ]);
            } else {
                // No more transactions in queue
                Notification::make()
                    ->title("No more customers in queue.")
                    ->warning()
                    ->send();
            }

            DB::commit();
            $this->loadQueueData();
        } catch (\Exception $e) {
            DB::rollBack();
            Notification::make()
                ->title("Error: " . $e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function recall()
    {
        if ($this->currentTransaction) {
            $queueNumber = $this->currentTransaction->queue->number;
            $queueNumber = str_pad($queueNumber, 3, '0', STR_PAD_LEFT);

            $customerName = $this->currentTransaction->customer->name;
            // Broadcast the event to refresh all connected clients
            $this->dispatch('queueUpdated', [
                'queueNumber' => $queueNumber,
                'customerName' => $customerName
                // ])->to('queue-channel');
                ])->to('queue-display');

            // Emit an event for the speech synthesis
            $this->dispatch('speakQueue', [
                'queueNumber' => $queueNumber,
                'customerName' => $customerName,
                'counterName' => $this->counterName,
                'isRecall' => true
            ]);

            event(new QueueUpdated($queueNumber, $customerName));

            Log::info("Recalling Queue #$queueNumber - $customerName");
            Notification::make()
                ->title("Recalled Queue #$queueNumber - $customerName")
                ->success()
                ->send();
        } else {
            Notification::make()
                ->title("No current customer to recall.")
                ->warning()
                ->send();
        }
    }

    public function markComplete()
    {
        if ($this->currentTransaction) {
            $this->currentTransaction->status = 'done';
            $this->currentTransaction->date = now();
            $this->currentTransaction->save();

            $queueNumber = $this->currentTransaction->queue->number;

            Notification::make()
                ->title("Marked Queue #$queueNumber as complete")
                ->success()
                ->send();

            $this->loadQueueData();

            // Broadcast the event to refresh all connected clients
            // In the markComplete() method
            $this->dispatch('refreshQueue')->to('public-queue-display');
            // $this->dispatch('refreshQueue')->to('queue-channel');
        } else {
            Notification::make()
                ->title("No current customer to mark as complete.")
                ->warning()
                ->send();
        }
    }

    public function render()
    {
        return view('livewire.queue-display');
    }
}
