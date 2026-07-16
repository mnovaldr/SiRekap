<x-filament-panels::page>
<div class="space-y-6">

    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        
        {{-- SECTION 1: Sinkronisasi Otomatis MCC Event --}}
        <x-filament::section heading="Sinkronisasi Otomatis MCC Event" description="Tarik data event yang sudah disetujui secara real-time dari website MCC">
            <form wire:submit.prevent="startSync" class="space-y-4">
                <div>
                    <label for="username" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username / Email MCC Event</label>
                    <div class="mt-1">
                        <input type="text" id="username" wire:model.defer="username" placeholder="Masukkan username admin..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white" required>
                    </div>
                </div>

                <div>
                    <label for="password" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Password MCC Event</label>
                    <div class="mt-1">
                        <input type="password" id="password" wire:model.defer="password" placeholder="Masukkan password..."
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white" required>
                    </div>
                </div>

                <div class="flex items-center gap-3">
                    <x-filament::button type="submit" color="primary" icon="heroicon-o-arrow-path" wire:loading.attr="disabled">
                        <span wire:loading.remove>Sinkronkan Sekarang</span>
                        <span wire:loading>Memproses Sinkronisasi...</span>
                    </x-filament::button>
                    
                    <span class="text-xs text-gray-500 dark:text-gray-400">
                        *Gunakan username `demo@mcc.go.id` dan password `demo123` untuk mode demo/simulasi.
                    </span>
                </div>
            </form>
        </x-filament::section>

        {{-- SECTION 2: Buat Link Kunjungan Baru --}}
        <x-filament::section heading="Buat Link Kunjungan / Presentasi" description="Buat token akses khusus proyektor tanpa login untuk tamu VIP/DPR">
            <form wire:submit.prevent="generateToken" class="space-y-4">
                <div>
                    <label for="label" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nama Kunjungan / Instansi</label>
                    <div class="mt-1">
                        <input type="text" id="label" wire:model.defer="label" placeholder="Contoh: Kunjungan DPR RI Komisi X"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white" required>
                    </div>
                </div>

                <div>
                    <label for="duration" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Masa Aktif Link (Jam)</label>
                    <div class="mt-1">
                        <select id="duration" wire:model.defer="duration"
                            class="block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm dark:bg-gray-800 dark:border-gray-700 dark:text-white">
                            <option value="1">1 Jam</option>
                            <option value="3">3 Jam</option>
                            <option value="6">6 Jam</option>
                            <option value="12">12 Jam</option>
                            <option value="24">24 Jam</option>
                        </select>
                    </div>
                </div>

                <div>
                    <x-filament::button type="submit" color="success" icon="heroicon-o-link">
                        Buat Link Kunjungan
                    </x-filament::button>
                </div>
            </form>
        </x-filament::section>

    </div>

    {{-- SECTION 3: Daftar Link Kunjungan Aktif --}}
    <x-filament::section heading="Daftar Link Kunjungan Aktif" description="Kelola hak akses layar proyektor. Nonaktifkan atau salin link dari sini.">
        <div class="overflow-x-auto">
            <table class="w-full text-left border-collapse sm:text-sm">
                <thead>
                    <tr class="border-b border-gray-200 dark:border-gray-700 text-gray-500 dark:text-gray-400">
                        <th class="py-3 px-4 font-semibold">Nama Kunjungan</th>
                        <th class="py-3 px-4 font-semibold">Masa Aktif</th>
                        <th class="py-3 px-4 font-semibold">Status Kevalidan</th>
                        <th class="py-3 px-4 font-semibold text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-150 dark:divide-gray-800 text-gray-900 dark:text-gray-100">
                    @forelse($this->getActiveTokens() as $token)
                        @php
                            $isValid = $token->isValid();
                            $url = url('/share/kunjungan?token=' . $token->token);
                        @endphp
                        <tr>
                            <td class="py-3 px-4 font-medium">{{ $token->label }}</td>
                            <td class="py-3 px-4">
                                <span class="text-xs text-gray-500 dark:text-gray-400">
                                    Sampai: {{ $token->expires_at ? $token->expires_at->format('d M Y, H:i') . ' WIB' : 'Selamanya' }}
                                </span>
                            </td>
                            <td class="py-3 px-4">
                                @if($isValid)
                                    <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">
                                        Aktif & Valid
                                    </span>
                                @else
                                    <span class="inline-flex items-center rounded-md bg-red-50 px-2 py-1 text-xs font-medium text-red-700 ring-1 ring-inset ring-red-600/20 dark:bg-red-500/10 dark:text-red-400 dark:ring-red-500/20">
                                        Expired / Nonaktif
                                    </span>
                                @endif
                            </td>
                            <td class="py-3 px-4 text-right space-x-2">
                                <x-filament::button size="sm" color="info" icon="heroicon-o-clipboard-document"
                                    x-on:click="window.navigator.clipboard.writeText('{{ $url }}'); $tooltip('Link berhasil disalin!', { timeout: 1500 });">
                                    Salin Link
                                </x-filament::button>

                                <x-filament::button size="sm" color="{{ $token->is_active ? 'warning' : 'success' }}" 
                                    wire:click="toggleToken({{ $token->id }})">
                                    {{ $token->is_active ? 'Matikan' : 'Aktifkan' }}
                                </x-filament::button>

                                <x-filament::button size="sm" color="danger" icon="heroicon-o-trash"
                                    x-on:click="if(confirm('Hapus link kunjungan ini?')) { $wire.deleteToken({{ $token->id }}) }">
                                    Hapus
                                </x-filament::button>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-gray-500 dark:text-gray-400">
                                Belum ada link kunjungan yang dibuat. Silakan buat satu di atas.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </x-filament::section>

</div>
</x-filament-panels::page>
