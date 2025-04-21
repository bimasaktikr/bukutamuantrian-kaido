<x-filament::page>
    <div class="grid grid-cols-1 gap-6">
        <div class="relative overflow-hidden transition-all duration-300 bg-white border rounded-xl">
            <div class="p-6 space-y-6">
                <!-- Counter and Total Queue -->
                <div class="flex items-center justify-between">
                    <div class="flex items-center space-x-2">
                        <span class="text-2xl font-semibold text-gray-600">Counter 1 </span>
                        {{-- <span class="text-4xl font-bold text-primary-600">1</span> --}}
                    </div>

                    <div>
                        <span class="text-lg font-medium text-gray-500">Total Queue: {{ count($queues) }}</span>
                    </div>
                </div>

                <!-- Now Serving Section -->
                <div class="space-y-6">
                    <div class="space-y-4">
                        <span class="text-xl font-semibold text-gray-600">Now Serving</span>
                        @php
                            $currentServing = $queues->where('status', 'onprocess')->first();
                            if ($currentServing) {
                                $currentServingNumber = $queues->where('status', 'onprocess')->first()?->number ?? '-';
                                $currentServingNumber = str_pad($currentServingNumber, 3, '0', STR_PAD_LEFT);
                                $service = $currentServing->transaction->service->code."-";
                            } else {
                                $currentServingNumber = '000';
                                $service = ' ';
                            }
                        @endphp
                        <div class="flex flex-col items-center justify-center py-8">
                            <div class="text-2xl font-extrabold tracking-tight text-primary-600">
                                {{ $service . $currentServingNumber }}
                            </div>
                        </div>
                    </div>

                    <!-- Operator Section -->
                    <div class="space-y-2">
                        <span class="text-2xl font-semibold text-gray-600">Operator</span>
                        @php
                            $currentOperator = $currentServing;
                            if ($currentServing) {
                                $currentOperator = $currentServing->operator;
                            } else {
                                $currentOperator = null;
                            }

                        @endphp
                        <div class="flex items-center space-x-2">
                            <span class="text-4xl font-bold text-primary-600">
                                {{-- {{ $currentOperator?->name ?? 'UnAssign' }} --}}
                                {{ $currentOperator -> name ?? 'UnAssign' }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament::page>
