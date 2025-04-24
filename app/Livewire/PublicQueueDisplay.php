<?php

namespace App\Livewire;

use App\Models\Transaction;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Log;
use Livewire\Component;

class PublicQueueDisplay extends Component
{
    public $currentTransaction = null;
    public $queueList = [];
    public $completedList = [];
    public $counterName = "Counter 1";
    public $videoUrl = "https://www.youtube.com/embed/38-B6ihKXVg?si=tZiQhAgs2OJFTLKi?autoplay=1&mute=1&loop=1";
    // https://www.youtube.com/watch?v=38-B6ihKXVg

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
            ->whereDate('date', now())
            ->with(['queue', 'customer', 'service', 'purpose'])
            ->orderBy('updated_at', 'desc')
            ->limit(10)
            ->get();
    }

    public function handleQueueUpdate()
    {
        Log::info('queueUpdated listener triggered');
        $this->loadQueueData();
        // $this->refresh();
        $this->dispatch('playAlert');
    }

    public function render()
    {
        return view('livewire.public-queue-display');
    }
}
