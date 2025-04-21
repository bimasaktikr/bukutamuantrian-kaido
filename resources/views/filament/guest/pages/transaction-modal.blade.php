<div>
    <x-filament::modal id="transactionModal" :is-open="true">
        <x-slot name="heading">
            Transaction Details
        </x-slot>

        <div>
            <p><strong>Name:</strong> {{ $customer }}</p>
            <p><strong>Service:</strong> {{ $transaction }}</p>
            <p><strong>Queue Number:</strong> {{ $queue}}</p>
            <p><strong>Queue Date:</strong> {{ $queue }}</p>
            <p><strong>Media Layanan:</strong> {{ $transaction }}</p>
        </div>

        <x-slot name="footer">
            <x-filament::button wire:click="$emit('closeModal')" color="secondary">
                Close
            </x-filament::button>
            <x-filament::button wire:click="downloadPdf" color="primary">
                Download PDF
            </x-filament::button>
        </x-slot>
    </x-filament::modal>
</div>
