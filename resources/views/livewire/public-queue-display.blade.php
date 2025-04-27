<div class="w-full text-gray-900 bg-gray-100 dark:bg-gray-900 dark:text-gray-100" wire:poll.5s="loadQueueData">
    <div class="grid h-screen gap-4 p-4 md:grid-cols-2 md:grid-rows-3">

        <!-- Tampilan Video (2 kolom, 2 baris) -->
        <div class="col-span-1 row-span-2 overflow-hidden bg-white rounded-lg shadow-lg dark:bg-gray-800">
            <iframe
            class="w-full h-full"
            src="{{ $videoUrl }}"
            frameborder="0"
            allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
            allowfullscreen>
        </iframe>
        </div>

 <!-- Tampilan Nomor Saat Ini -->
<div class="flex flex-col justify-between col-span-1 row-span-2 p-6 bg-white shadow-2xl rounded-2xl dark:bg-gray-800">
    <div class="text-center">
        <h2 class="text-3xl font-extrabold tracking-wide text-gray-800 dark:text-white">
            {{ $counterName }}
        </h2>
        <p class="mt-2 text-lg text-gray-500 dark:text-gray-300">
            Sedang Dilayani
        </p>
    </div>

    @if($currentTransaction)
        <div class="flex flex-col items-center justify-center flex-grow mt-10">
            <div class="text-[10rem] font-extrabold text-blue-600 dark:text-blue-400 drop-shadow-lg leading-none">
                {{ $currentTransaction->queue->number }}
            </div>
            <div class="mt-8 text-[4rem] font-bold text-gray-700 dark:text-white tracking-wide text-center leading-tight">
                {{ strtoupper($currentTransaction->customer->name) }}
            </div>
        </div>
    @else
        <div class="flex flex-col items-center justify-center flex-grow mt-8">
            <p class="text-4xl text-gray-400 dark:text-gray-500">
                Belum Ada Pelanggan
            </p>
        </div>
    @endif
</div>



        <!-- Tampilan Daftar Antrian (2 kolom, 1 baris) -->
        {{-- <div class="row-start-3 p-4 bg-white rounded-lg shadow-lg md:col-span-2 dark:bg-gray-800">
            <div class="grid h-full grid-cols-2">
                <!-- Antrian Menunggu -->
                <div class="pr-4 border-r border-gray-200 dark:border-gray-700">
                    <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white">Antrian Menunggu</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @forelse($queueList as $transaction)
                            <div class="flex items-center p-3 rounded-lg bg-blue-50 dark:bg-blue-900">
                                <div class="mr-4 text-3xl font-bold text-blue-600 dark:text-blue-300">
                                    {{ $transaction->queue->number }}
                                </div>
                                <div class="truncate">
                                    <div class="font-medium truncate">{{ $transaction->customer->name }}</div>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center col-span-2 py-6 text-gray-500 dark:text-gray-400">
                                Tidak ada pelanggan menunggu
                            </div>
                        @endforelse
                    </div>
                </div>

                <!-- Antrian Selesai -->
                <div class="pl-4">
                    <h3 class="mb-2 text-xl font-bold text-gray-800 dark:text-white">Baru Saja Selesai</h3>
                    <div class="grid grid-cols-2 gap-4">
                        @forelse($completedList as $transaction)
                            <div class="flex items-center p-3 rounded-lg bg-green-50 dark:bg-green-900">
                                <div class="mr-4 text-3xl font-bold text-green-600 dark:text-green-300">
                                    {{ $transaction->queue->number }}
                                </div>
                                <div class="truncate">
                                    <div class="font-medium truncate">{{ $transaction->customer->name }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-300">
                                        {{ $transaction->updated_at->format('h:i A') }}
                                    </div>
                                </div>
                            </div>
                        @empty
                            <div class="flex items-center justify-center col-span-2 py-6 text-gray-500 dark:text-gray-400">
                                Tidak ada transaksi selesai hari ini
                            </div>
                        @endforelse
                    </div>
                </div>
            </div>
        </div> --}}
        <div class="row-start-3 p-4 bg-white rounded-lg shadow-lg md:col-span-2 dark:bg-gray-800">
            <div class="grid grid-cols-1 gap-6 md:grid-cols-2">

                <!-- Antrian Menunggu -->
                <div class="flex flex-col h-full p-4 rounded-lg shadow-md bg-gray-50 dark:bg-gray-900">
                    <h3 class="mb-4 text-2xl font-bold text-blue-600 dark:text-blue-300">Antrian Menunggu</h3>

                    @if($queueList->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-gray-900 dark:text-gray-100">
                                <thead class="text-xs text-gray-700 uppercase bg-blue-100 dark:bg-blue-800 dark:text-gray-300">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Nomor</th>
                                        <th class="px-4 py-2 text-left">Nama</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($queueList as $transaction)
                                        <tr class="border-b dark:border-gray-700">
                                            <td class="px-4 py-3 font-bold text-blue-600 dark:text-blue-400">
                                                {{ $transaction->queue->number }}
                                            </td>
                                            <td class="px-4 py-3 truncate">
                                                {{ $transaction->customer->name }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="flex items-center justify-center flex-1 text-gray-500 dark:text-gray-400">
                            Tidak ada pelanggan menunggu
                        </div>
                    @endif
                </div>

                <!-- Antrian Selesai -->
                <div class="flex flex-col h-full p-4 rounded-lg shadow-md bg-gray-50 dark:bg-gray-900">
                    <h3 class="mb-4 text-2xl font-bold text-green-600 dark:text-green-300">Baru Saja Selesai</h3>

                    @if($completedList->isNotEmpty())
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm text-gray-900 dark:text-gray-100">
                                <thead class="text-xs text-gray-700 uppercase bg-green-100 dark:bg-green-800 dark:text-gray-300">
                                    <tr>
                                        <th class="px-4 py-2 text-left">Nomor</th>
                                        <th class="px-4 py-2 text-left">Nama</th>
                                        <th class="px-4 py-2 text-left">Waktu</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($completedList as $transaction)
                                        <tr class="border-b dark:border-gray-700">
                                            <td class="px-4 py-3 font-bold text-green-600 dark:text-green-400">
                                                {{ $transaction->queue->number }}
                                            </td>
                                            <td class="px-4 py-3 truncate">
                                                {{ $transaction->customer->name }}
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-500 dark:text-gray-400">
                                                {{ $transaction->updated_at->format('h:i A') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @else
                        <div class="flex items-center justify-center flex-1 text-gray-500 dark:text-gray-400">
                            Tidak ada transaksi selesai hari ini
                        </div>
                    @endif
                </div>

            </div>
        </div>

    </div>

    <!-- Pusher untuk pembaruan real-time -->
    {{-- <script>
        document.addEventListener('DOMContentLoaded', () => {
            window.Echo.channel('queue-channel')
                .listen('.queueUpdated', (event) => {
                    console.log("📣 Event antrian diterima", event);
                    window.Livewire.dispatch('queueUpdated', event.data);
                });
        });
    </script> --}}
</div>
