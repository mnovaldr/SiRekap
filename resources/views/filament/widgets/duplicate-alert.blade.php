<x-filament-widgets::widget>
    @if($this->getTotalIssues() > 0)
        <x-filament::section
            :heading="'⚠️ Perhatian: Data Perlu Direview'"
            :description="'Ditemukan ' . $this->getDuplicateCount() . ' data duplikat dan ' . $this->getReviewCount() . ' data perlu review'"
            icon="heroicon-o-exclamation-triangle"
            icon-color="warning"
        >
            <div class="flex items-center justify-between">
                <div class="flex gap-4">
                    <div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Duplikat:</span>
                        <span class="ml-2 text-lg font-bold text-red-600 dark:text-red-400">{{ $this->getDuplicateCount() }}</span>
                    </div>
                    <div>
                        <span class="text-sm text-gray-600 dark:text-gray-400">Review:</span>
                        <span class="ml-2 text-lg font-bold text-yellow-600 dark:text-yellow-400">{{ $this->getReviewCount() }}</span>
                    </div>
                </div>
                <x-filament::button
                    tag="a"
                    href="{{ url('/admin/cek-duplikat') }}"
                    color="warning"
                    size="sm"
                >
                    Lihat & Perbaiki
                </x-filament::button>
            </div>
        </x-filament::section>
    @endif
</x-filament-widgets::widget>
