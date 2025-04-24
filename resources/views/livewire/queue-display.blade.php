<div>
    <div class="grid grid-cols-3 gap-4">
        <!-- Current Transaction & Controls -->
        <div class="col-span-1 p-4 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <div class="flex flex-col space-y-4">
                <h2 class="text-xl font-bold text-gray-800 dark:text-white">{{ $counterName }}</h2>

                <div class="p-4 mb-4 rounded-lg bg-blue-50 dark:bg-blue-900">
                    @if($currentTransaction)
                        <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Current Customer</h3>
                        <div class="mt-2">
                            <div class="text-4xl font-bold text-blue-600 dark:text-blue-300">
                                Queue #{{ $currentTransaction->queue->number }}
                            </div>
                            <div class="mt-1 text-xl font-medium dark:text-white">
                                {{ $currentTransaction->customer->name }}
                            </div>
                            <div class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                                {{ $currentTransaction->service->name }} - {{ $currentTransaction->purpose->name }}
                            </div>
                        </div>
                    @else
                        <div class="py-6 text-center">
                            <div class="text-xl text-gray-500 dark:text-gray-400">No customer currently being served</div>
                        </div>
                    @endif
                </div>

                <!-- Controls -->
                <div class="flex flex-col space-y-2">
                    <button
                        wire:click="callNext"
                        class="w-full py-3 font-bold text-white transition-colors duration-150 bg-blue-600 rounded-lg hover:bg-blue-700"
                    >
                        Call Next
                    </button>

                    <div class="grid grid-cols-2 gap-2">
                        <button
                            wire:click="recall"
                            class="py-2 font-bold text-white transition-colors duration-150 bg-yellow-500 rounded-lg hover:bg-yellow-600"
                            @if(!$currentTransaction) disabled @endif
                        >
                            Recall
                        </button>

                        <button
                            wire:click="markComplete"
                            class="py-2 font-bold text-white transition-colors duration-150 bg-green-500 rounded-lg hover:bg-green-600"
                            @if(!$currentTransaction) disabled @endif
                        >
                            Mark Complete
                        </button>
                    </div>
                </div>

                <!-- Next Customer Preview -->
                <div class="p-4 mt-4 rounded-lg bg-gray-50 dark:bg-gray-700">
                    <h3 class="text-lg font-semibold text-gray-700 dark:text-gray-200">Next in Queue</h3>
                    @if($nextInQueue)
                        <div class="mt-2">
                            <div class="text-2xl font-bold text-gray-700 dark:text-white">
                                Queue #{{ $nextInQueue->queue->number ?? '-' }}
                            </div>
                            <div class="text-lg font-medium dark:text-gray-200">
                                {{ $nextInQueue->customer->name }}
                            </div>
                            <div class="text-sm text-gray-500 dark:text-gray-300">
                                {{ $nextInQueue->service->name }} - {{ $nextInQueue->purpose->name }}
                            </div>
                        </div>
                    @else
                        <div class="py-4 text-center">
                            <div class="text-gray-500 dark:text-gray-400">No customers waiting</div>
                        </div>
                    @endif
                </div>
            </div>
        </div>

        <!-- Queue List -->
        <div class="col-span-2 p-4 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <div class="flex flex-col h-full">
                <h2 class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Waiting Queue</h2>

                <div class="flex-grow overflow-y-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                        <thead class="bg-gray-50 dark:bg-gray-700">
                            <tr>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Queue #</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Customer</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Service</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Purpose</th>
                                <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Wait Time</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                            @forelse($queueList as $transaction)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-lg font-bold text-blue-600 dark:text-blue-300">{{ $transaction->queue->number ?? '-' }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="font-medium text-gray-900 dark:text-white">{{ $transaction->customer->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ $transaction->service->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ $transaction->purpose->name }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm text-gray-900 dark:text-gray-200">{{ $transaction->created_at->diffForHumans(null, true) }}</div>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No customers in queue</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Completed Transactions -->
        <div class="col-span-3 p-4 mt-4 bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <h2 class="mb-4 text-xl font-bold text-gray-800 dark:text-white">Completed Today</h2>

            <div class="overflow-x-auto">
                <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-600">
                    <thead class="bg-gray-50 dark:bg-gray-700">
                        <tr>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Queue #</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Customer</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Service</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Purpose</th>
                            <th class="px-6 py-3 text-xs font-medium tracking-wider text-left text-gray-500 uppercase dark:text-gray-300">Completed At</th>
                        </tr>
                    </thead>
                    <tbody class="bg-white divide-y divide-gray-200 dark:bg-gray-800 dark:divide-gray-700">
                        @forelse($completedList as $transaction)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-lg font-medium text-gray-900 dark:text-white">{{ $transaction->queue->number }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="font-medium text-gray-900 dark:text-white">{{ $transaction->customer->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">{{ $transaction->service->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">{{ $transaction->purpose->name }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm text-gray-900 dark:text-gray-200">{{ $transaction->updated_at->format('h:i A') }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="5" class="px-6 py-4 text-center text-gray-500 dark:text-gray-400">No completed transactions today</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <!-- JavaScript for Speech -->
    <script>
        document.addEventListener('livewire:initialized', () => {
            @this.on('speakQueue', (data) => {
                console.log('Received data:', data[0].queueNumber);
                if ('speechSynthesis' in window) {
                    let message = data[0].isRecall
                        ? `Memanggil Kembali customer dengan nomor ${data[0].queueNumber}, atas nama ${data[0].customerName}, silahkan ke ${data[0].counterName}`
                        : `Customer number ${data[0].queueNumber}, atas nama ${data[0].customerName}, silahkan ke ${data[0].counterName}`;
                    const utterance = new SpeechSynthesisUtterance(message);
                    console.log('🔊 Speaking:', message);
                    utterance.lang = 'id-ID';
                    utterance.rate = 0.9;
                    utterance.pitch = 1;
                    utterance.volume = 5;
                    window.speechSynthesis.speak(utterance);
                }
            });
        });
    </script>
</div>
