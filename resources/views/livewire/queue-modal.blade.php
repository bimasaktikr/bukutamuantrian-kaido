<div
    x-data="{ open: @entangle('showQueueModal') }"
    x-show="open"
    x-transition
    class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-50"
>
    <div class="w-full max-w-md p-6 bg-white rounded-lg shadow-lg">
        <h2 class="mb-4 text-2xl font-bold text-center">Nomor Antrian Anda</h2>

        <div class="flex flex-col items-center justify-center space-y-4">
            <div class="font-bold text-blue-600 text-7xl">
                @if ($transaction)
                    {{ $transaction->service->code . '-' . $transaction->queue->number  }}
                @endif
            </div>
            <div class="text-xl font-medium text-gray-700">
                {{ $transaction -> customer -> name ?? '-' }}
            </div>
        </div>

        <div class="flex justify-center mt-6">
            <button
                wire:click="redirectToPublic"
                class="px-6 py-2 text-black transition bg-blue-600 rounded-lg hover:bg-blue-700"
            >
                OK
            </button>
        </div>
    </div>
</div>
