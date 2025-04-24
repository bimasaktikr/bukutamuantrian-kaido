<?php

namespace App\Http\Livewire;

use App\Models\Queue;
use Livewire\Component;
use Illuminate\Support\Carbon;

class OperatorQueueCaller extends Component
{
    public $currentQueue;

    public function mount()
    {
        $this->loadCurrentQueue();
    }

    public function loadCurrentQueue()
    {
        $this->currentQueue = Queue::with('transaction.customer', 'transaction.service')
            ->whereDate('created_at', Carbon::today())
            ->where('status', 'queue')
            ->orderBy('number')
            ->first();
    }

    public function callQueue()
    {
        if ($this->currentQueue) {
            $this->currentQueue->update(['status' => 'onprocess']);
            $this->dispatchBrowserEvent('call-queue', [
                'prefix' => $this->currentQueue->transaction->service->code,
                'number' => $this->currentQueue->number,
                'name' => $this->currentQueue->transaction->customer->name,
            ]);

            $this->loadCurrentQueue();
        }
    }

    public function render()
    {
        return view('livewire.operator-queue-caller');
    }
}
