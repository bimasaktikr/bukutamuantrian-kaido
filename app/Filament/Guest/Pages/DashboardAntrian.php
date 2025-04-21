<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Queue;
use Carbon\Carbon;
use Livewire\Component;

class DashboardAntrian extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-queue-list';
    protected static string $view = 'filament.guest.pages.dashboard-antrian';
    public $queues;

    public function mount()
    {
        $this->queues = Queue::where('date', Carbon::today())
            ->orderBy('number')
            ->get();
    }

    public function getListeners()
    {
        return [
            'echo:queues,QueueUpdated' => 'refreshQueues'
        ];
    }

    public function refreshQueues()
    {
        $this->queues = Queue::where('status', 'queue')
            ->orderBy('number')
            ->get();
    }
}
