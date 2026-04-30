<x-filament-panels::page>
    <div class="space-y-10 max-w-[95rem] mx-auto">
        {{-- Header & Summary --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-end gap-6 bg-white dark:bg-gray-900 p-8 rounded-3xl border border-gray-100 dark:border-gray-800 shadow-sm">
            <div class="space-y-1">
                <h2 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white uppercase tracking-tighter">Audit Integritas Data</h2>
                <p class="text-gray-500 dark:text-gray-400 font-medium italic text-xs">Pendeteksian otomatis duplikat penuh dan konflik multi-ruangan.</p>
            </div>
            <div class="flex items-center gap-3">
                <x-filament::button wire:click="analyze" icon="heroicon-o-arrow-path" color="gray" size="sm" class="rounded-xl">
                    Re-Scan Database
                </x-filament::button>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            @foreach([
                [
                    'label' => 'Duplikat Penuh', 
                    'count' => $duplikatCount, 
                    'sub' => 'Harus dihapus (Key Full Identik)',
                    'color' => 'red', 
                    'icon' => 'heroicon-o-document-duplicate',
                    'gradient' => 'from-red-600 to-red-800'
                ],
                [
                    'label' => 'Perlu Review', 
                    'count' => $reviewCount, 
                    'sub' => 'Multi-ruangan (Key Short Identik)',
                    'color' => 'amber', 
                    'icon' => 'heroicon-o-eye-slash',
                    'gradient' => 'from-amber-500 to-amber-700'
                ],
                [
                    'label' => 'Total Isu Terdeteksi', 
                    'count' => $totalIssues, 
                    'sub' => 'Anomali data dalam database',
                    'color' => 'blue', 
                    'icon' => 'heroicon-o-shield-exclamation',
                    'gradient' => 'from-blue-600 to-blue-800'
                ]
            ] as $stat)
            <div class="relative overflow-hidden bg-gradient-to-br {{ $stat['gradient'] }} rounded-[2rem] p-8 shadow-lg group hover:scale-[1.02] transition-all duration-500">
                <div class="absolute -right-4 -top-4 w-24 h-24 bg-white opacity-10 rounded-full group-hover:scale-150 transition-transform duration-700"></div>
                <div class="flex items-start justify-between relative z-10">
                    <div class="space-y-1">
                        <p class="text-[10px] font-black uppercase tracking-[0.2em] text-white opacity-80">{{ $stat['label'] }}</p>
                        <p class="text-5xl font-black text-white">{{ $stat['count'] }}</p>
                        <p class="text-[10px] font-bold text-white opacity-70 mt-2">{{ $stat['sub'] }}</p>
                    </div>
                    <div class="p-3 bg-white/20 rounded-2xl backdrop-blur-md">
                        <x-dynamic-component :component="$stat['icon']" class="w-8 h-8 text-white" />
                    </div>
                </div>
            </div>
            @endforeach
        </div>

        {{-- Main Analysis Area --}}
        @if(count($duplikatGroups) > 0)
            <div class="space-y-8">
                @foreach($duplikatGroups as $index => $group)
                    <div class="bg-white dark:bg-gray-900 rounded-[2.5rem] border border-gray-100 dark:border-gray-800 shadow-sm overflow-hidden border-l-8 border-l-{{ str_contains($group['type'], 'Hapus') ? 'red' : 'amber' }}-600">
                        <div class="px-10 py-8 border-b border-gray-50 dark:border-gray-800 bg-gray-50/50 dark:bg-gray-800/30 flex flex-col md:flex-row justify-between items-start md:items-center gap-6">
                            <div class="space-y-1">
                                <div class="flex items-center gap-3">
                                    <span class="text-[10px] font-black uppercase tracking-widest px-3 py-1 rounded-full bg-{{ str_contains($group['type'], 'Hapus') ? 'red' : 'amber' }}-50 text-{{ str_contains($group['type'], 'Hapus') ? 'red' : 'amber' }}-700 dark:bg-{{ str_contains($group['type'], 'Hapus') ? 'red' : 'amber' }}-900/30">
                                        {{ $group['type'] }}
                                    </span>
                                    <span class="text-[10px] font-bold text-gray-400 italic">ID: {{ substr(md5($group['key']), 0, 8) }}</span>
                                </div>
                                <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight mt-2">{{ $group['reason'] }}</h3>
                            </div>
                            <div class="flex items-center gap-3">
                                @if(str_contains($group['type'], 'Hapus'))
                                    <x-filament::button wire:click="processGroup({{ $index }})" color="danger" size="sm" icon="heroicon-o-trash" icon-position="after">
                                        Hapus Semua Duplikat (Simpan 1)
                                    </x-filament::button>
                                @else
                                    <x-filament::button wire:click="processGroup({{ $index }})" color="success" size="sm" icon="heroicon-o-check-badge" icon-position="after">
                                        Validasi Semua (Multi-Ruangan)
                                    </x-filament::button>
                                @endif
                            </div>
                        </div>

                        <div class="overflow-x-auto">
                            <table class="w-full text-left">
                                <thead>
                                    <tr class="text-[10px] font-black uppercase tracking-[0.2em] text-gray-400 border-b border-gray-50 dark:border-gray-800 bg-white dark:bg-gray-900">
                                        <th class="px-10 py-5">Event Details</th>
                                        <th class="px-10 py-5">Spatial Info</th>
                                        <th class="px-10 py-5">Status</th>
                                        <th class="px-10 py-5 text-right">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-gray-50 dark:divide-gray-800">
                                    @foreach($group['items'] as $itemIndex => $item)
                                        <tr class="hover:bg-gray-50/50 dark:hover:bg-gray-800/30 transition-colors">
                                            <td class="px-10 py-6">
                                                <p class="text-xs font-black text-gray-900 dark:text-white">{{ $item['judul_kegiatan'] }}</p>
                                                <p class="text-[10px] font-bold text-gray-400 mt-1">{{ $item['nama_organisasi'] }}</p>
                                                <p class="text-[9px] font-black text-blue-600 mt-0.5 uppercase tracking-widest italic">{{ $item['tanggal'] }}</p>
                                            </td>
                                            <td class="px-10 py-6">
                                                <div class="space-y-1">
                                                    <span class="text-[10px] font-black text-gray-600 dark:text-gray-400 bg-gray-100 dark:bg-gray-800 px-2 py-1 rounded-lg">
                                                        {{ $item['lokasi'] }}
                                                    </span>
                                                    <p class="text-[9px] font-bold text-gray-400 px-1">Lantai {{ $item['lantai'] }}</p>
                                                </div>
                                            </td>
                                            <td class="px-10 py-6">
                                                <span class="text-[9px] font-black uppercase tracking-widest 
                                                    {{ $item['status_data'] === 'Valid' ? 'text-emerald-600' : ($item['status_data'] === 'Duplikat' ? 'text-red-600' : 'text-amber-600') }}">
                                                    ● {{ $item['status_data'] }}
                                                </span>
                                            </td>
                                            <td class="px-10 py-6 text-right">
                                                <div class="flex justify-end gap-2">
                                                    @if($item['status_data'] !== 'Valid')
                                                        <x-filament::icon-button 
                                                            wire:click="validateRow('{{ $item['id'] }}')"
                                                            icon="heroicon-o-check"
                                                            color="success"
                                                            tooltip="Validasi Baris Ini"
                                                            size="sm"
                                                        />
                                                    @endif
                                                    <x-filament::icon-button 
                                                        wire:click="deleteRow('{{ $item['id'] }}')"
                                                        wire:confirm="Hapus data ini secara permanen?"
                                                        icon="heroicon-o-trash"
                                                        color="danger"
                                                        tooltip="Hapus Baris Ini"
                                                        size="sm"
                                                    />
                                                </div>
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <div class="flex flex-col items-center justify-center py-40 bg-white dark:bg-gray-900 rounded-[3rem] border border-gray-100 dark:border-gray-800 shadow-sm text-center px-10">
                <div class="w-32 h-32 bg-emerald-50 dark:bg-emerald-900/20 rounded-[3rem] flex items-center justify-center mb-10 relative">
                    <div class="absolute inset-0 bg-emerald-500/20 blur-[40px] rounded-full animate-pulse"></div>
                    <x-heroicon-o-shield-check class="w-16 h-16 text-emerald-600 relative" />
                </div>
                <h3 class="text-2xl font-black text-gray-900 dark:text-white uppercase tracking-tighter">Database Clean</h3>
                <p class="text-gray-400 font-medium max-w-sm mt-3 leading-relaxed">Sistem tidak mendeteksi adanya duplikat atau konflik data multi-ruangan pada database saat ini. Integritas data terjamin.</p>
                <div class="mt-10">
                    <x-filament::button wire:click="analyze" color="gray" icon="heroicon-o-arrow-path">
                        Scan Ulang
                    </x-filament::button>
                </div>
            </div>
        @endif
    </div>
</x-filament-panels::page>
