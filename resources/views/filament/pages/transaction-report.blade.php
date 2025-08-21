<x-filament::page>
    <div class="grid grid-cols-1 gap-4 mb-6 sm:grid-cols-3">
        <div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
            <div class="text-sm text-gray-500 dark:text-gray-400">Total Rows</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">—</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
            <div class="text-sm text-gray-500 dark:text-gray-400">Period</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Use filters</div>
        </div>
        <div class="p-4 bg-white rounded-xl shadow dark:bg-gray-800">
            <div class="text-sm text-gray-500 dark:text-gray-400">Notes</div>
            <div class="text-2xl font-semibold text-gray-900 dark:text-gray-100">Export optional</div>
        </div>
    </div>

    <div class="overflow-hidden bg-white rounded-xl shadow dark:bg-gray-800">
        {{ $this->table }}
    </div>
</x-filament::page>
