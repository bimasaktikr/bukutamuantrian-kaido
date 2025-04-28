<?php

namespace App\Livewire;

use Illuminate\Support\Facades\Log;
use Livewire\Component;

class QueueModal extends Component
{
    public $showQueueModal = false;
    public $transaction;

    protected $listeners = ['showQueueModal' => 'handleShowQueueModal'];

    public function handleShowQueueModal($transaction_id)
    {
        $transaction = \App\Models\Transaction::with('service', 'queue', 'customer')
            ->find($transaction_id);
        // dd($transaction);
        if (!$transaction) {
            Log::error('Transaction not found for ID: ' . $transaction_id);
        }
        $this->transaction = $transaction;
        // dd($this->transaction->service->name);
        // Log::info('Transaction data received in QueueModal:', $this->transaction->toArray);
        $this->showQueueModal = true;
    }

    public function redirectToPublic()
    {
        return redirect()->route('filament.guest.pages.public');
    }

    public function render()
    {
        return view('livewire.queue-modal');
    }
}
