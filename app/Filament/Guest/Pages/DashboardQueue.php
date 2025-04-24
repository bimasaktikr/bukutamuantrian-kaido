<?php

namespace App\Filament\Guest\Pages;

use App\Models\Queue;
use App\Models\Service;
use Carbon\Carbon;
use Filament\Pages\Page;
use App\Services\QueueService;


class DashboardQueue extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static string $view = 'filament.guest.pages.dashboard-queue';

    protected static ?string $slug = 'antrian';


    public $queues;
    public $services;
    public $currentQueue;
    public $nextQueue;
    public $totalQueues;

    public function getTitle(): string
    {
        return ' ';
    }

    public function mount(QueueService $queueService): void
    {
        $todayQueues = $queueService->getTodayQueues();

        $this->queues = $todayQueues ?? collect();
        $this->currentQueue = $this->queues->firstWhere('status', 'onprocess');
        $this->nextQueue = $this->queues->firstWhere('status', 'queue');
        $this->totalQueues = $this->queues->count();

        $this->services = Service::orderBy('name')->get();
    }

}
