<div class="p-6 bg-white rounded shadow dark:bg-gray-800">
    @if ($currentQueue)
        <div class="mb-4 text-xl font-bold">
            {{ $currentQueue->transaction->service->code }}-{{ str_pad($currentQueue->number, 3, '0', STR_PAD_LEFT) }}
            <br>
            {{ $currentQueue->transaction->customer->name }}
        </div>

        <button
            wire:click="callQueue"
            class="px-4 py-2 text-white bg-blue-600 rounded hover:bg-blue-700"
        >
            Panggil Sekarang
        </button>
    @else
        <p class="text-gray-500 dark:text-gray-300">Tidak ada antrian.</p>
    @endif

    <script>
        window.addEventListener('call-queue', event => {
            const { prefix, number, name } = event.detail;
            const formattedNumber = number.toString().padStart(3, '0');
            const message = `Nomor Antrian ${prefix}-${formattedNumber} atas nama ${name} silahkan ke petugas`;

            const utterance = new SpeechSynthesisUtterance(message);
            utterance.lang = 'id-ID';
            speechSynthesis.speak(utterance);
        });
    </script>
</div>
