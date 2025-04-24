<x-filament::page>
    <div class="grid grid-cols-1 gap-6 md:grid-cols-3">
        {{-- Current Queue --}}
        <div class="flex items-center p-6 space-x-4 bg-white border border-blue-200 shadow dark:bg-gray-800 dark:border-blue-500 rounded-2xl">
            {{-- Icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-blue-500 dark:text-blue-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z" />
            </svg>
            {{-- Content --}}
            <div>
                <h3 class="text-xl font-semibold text-blue-600 dark:text-blue-300">Antrian Saat Ini</h3>
                @if($queues->where('status', 'onprocess')->first())
                    @php $current = $queues->where('status', 'onprocess')->first(); @endphp
                    <p class="mt-1 text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ $current->transaction->service->code }}-{{ str_pad($current->number, 3, '0', STR_PAD_LEFT) }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Nama: {{ $current->transaction->customer->name }}</p>
                @else
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Belum ada antrian diproses</p>
                @endif
            </div>
        </div>

        {{-- Next Queue --}}
        <div class="flex items-center p-6 space-x-4 bg-white border border-yellow-200 shadow dark:bg-gray-800 dark:border-yellow-500 rounded-2xl">
            {{-- Icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-yellow-500 dark:text-yellow-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6l4 2m4-6a8 8 0 11-16 0 8 8 0 0116 0z" />
            </svg>
            {{-- Content --}}
            <div>
                <h3 class="text-xl font-semibold text-yellow-600 dark:text-yellow-300">Antrian Berikutnya</h3>
                @php
                    $next = $queues->where('status', 'queue')->sortBy('number')->first();
                @endphp
                @if($next)
                    <p class="mt-1 text-lg font-bold text-gray-800 dark:text-gray-100">
                        {{ $next->transaction->service->code }}-{{ str_pad($next->number, 3, '0', STR_PAD_LEFT) }}
                    </p>
                    <p class="text-sm text-gray-600 dark:text-gray-400">Nama: {{ $next->transaction->customer->name }}</p>
                @else
                    <p class="mt-1 text-gray-500 dark:text-gray-400">Tidak ada antrian berikutnya</p>
                @endif
            </div>
        </div>

        {{-- Total Queue --}}
        <div class="flex items-center p-6 space-x-4 bg-white border border-green-200 shadow dark:bg-gray-800 dark:border-green-500 rounded-2xl">
            {{-- Icon --}}
            <svg xmlns="http://www.w3.org/2000/svg" class="w-10 h-10 text-green-500 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 7h18M3 12h18M3 17h18" />
            </svg>
            {{-- Content --}}
            <div>
                <h3 class="text-xl font-semibold text-green-600 dark:text-green-300">Total Antrian</h3>
                <p class="mt-1 text-3xl font-bold text-gray-800 dark:text-gray-100">
                    {{ $queues->count() }}
                </p>
            </div>
        </div>
    </div>
</x-filament::page>
