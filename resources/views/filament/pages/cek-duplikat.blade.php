<x-filament-panels::page>
    <style>
        /* Container & Typography */
        .integrity-wrapper { font-family: 'Inter', sans-serif; }
        
        /* Stat Cards - Ultra Minimalist */
        .stat-banner {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            padding: 1.5rem;
            display: flex;
            align-items: center;
            gap: 1.25rem;
        }
        .dark .stat-banner { background: #111827; border-color: #374151; }

        /* Grouping Section */
        .group-container {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 16px;
            overflow: hidden;
            margin-bottom: 1.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
        }
        .dark .group-container { background: #1f2937; border-color: #374151; }

        .group-header-bar {
            padding: 1rem 1.5rem;
            background: #f9fafb;
            border-bottom: 1px solid #e5e7eb;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .dark .group-header-bar { background: #111827; border-color: #374151; }

        /* Comparison Table Style */
        .comparison-grid {
            width: 100%;
            border-collapse: collapse;
            font-size: 0.8125rem;
        }
        .comparison-grid th {
            text-align: left;
            padding: 0.75rem 1rem;
            background: #fdfdfd;
            color: #6b7280;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            border-bottom: 1px solid #f3f4f6;
            font-size: 0.7rem;
        }
        .dark .comparison-grid th { background: #111827; color: #9ca3af; border-color: #374151; }
        
        .comparison-grid td {
            padding: 1rem;
            border-bottom: 1px solid #f9fafb;
            vertical-align: middle;
        }
        .dark .comparison-grid td { border-color: #374151; }

        .comparison-grid tr:last-child td { border-bottom: none; }
        
        /* Highlighting differences */
        .val-primary { color: #111827; font-weight: 600; }
        .dark .val-primary { color: #f3f4f6; }
        
        .val-secondary { color: #6b7280; font-weight: 400; }
        
        .badge-status {
            padding: 2px 8px;
            border-radius: 6px;
            font-size: 0.7rem;
            font-weight: 700;
        }
    </style>

    <div class="integrity-wrapper space-y-6">
        
        {{-- Stat Summary Bar --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="stat-banner">
                <div class="p-3 bg-rose-50 dark:bg-rose-900/30 rounded-xl">
                    <x-heroicon-o-document-duplicate class="w-6 h-6 text-rose-600" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ $duplikatCount }}</div>
                    <div class="text-[11px] text-gray-500 uppercase font-bold tracking-tight mt-1">Duplikat Identik</div>
                </div>
            </div>

            <div class="stat-banner">
                <div class="p-3 bg-amber-50 dark:bg-amber-900/30 rounded-xl">
                    <x-heroicon-o-arrows-right-left class="w-6 h-6 text-amber-600" />
                </div>
                <div>
                    <div class="text-2xl font-extrabold text-gray-900 dark:text-white leading-none">{{ $reviewCount }}</div>
                    <div class="text-[11px] text-gray-500 uppercase font-bold tracking-tight mt-1">Konflik Ruangan</div>
                </div>
            </div>

            <div class="stat-banner flex-1">
                 <x-filament::button wire:click="analyze" icon="heroicon-o-arrow-path" color="gray" class="w-full h-full rounded-xl border-dashed">
                    Refresh Analysis
                </x-filament::button>
            </div>
        </div>

        {{-- Content --}}
        @if(count($duplikatGroups) > 0)
            @php
                $groupIndex = $currentPage - 1;
                $group = $duplikatGroups[$groupIndex] ?? $duplikatGroups[0];
                $isHapus = str_contains($group['type'], 'Hapus');
                $accentColor = $isHapus ? '#e11d48' : '#d97706';
            @endphp
            
            <div class="group-container">
                <div class="group-header-bar" style="border-left: 4px solid {{ $accentColor }}">
                    <div class="flex items-center gap-3">
                        <span class="text-xs font-black uppercase px-2 py-1 rounded {{ $isHapus ? 'bg-rose-50 text-rose-700 dark:bg-rose-900/40' : 'bg-amber-50 text-amber-700 dark:bg-amber-900/40' }}">
                            {{ $isHapus ? 'DUPLIKAT' : 'REVIEW' }}
                        </span>
                        <span class="text-sm font-bold text-gray-700 dark:text-gray-300">{{ $group['reason'] }}</span>
                    </div>
                    
                    <x-filament::button 
                        wire:click="processGroup({{ $groupIndex }})" 
                        :color="$isHapus ? 'danger' : 'warning'" 
                        size="sm"
                        class="rounded-lg shadow-sm"
                    >
                        {{ $isHapus ? 'Simpan 1, Hapus Sisanya' : 'Validasi Seluruh Grup' }}
                    </x-filament::button>
                </div>

                <div class="overflow-x-auto">
                    <table class="comparison-grid">
                        <thead>
                            <tr>
                                <th>Organisasi / Kegiatan</th>
                                <th>Waktu</th>
                                <th>Lokasi & Ruang</th>
                                <th>Status Data</th>
                                <th class="text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($group['items'] as $item)
                                <tr class="hover:bg-gray-50/30 dark:hover:bg-gray-800/20 transition-colors">
                                    <td class="max-w-[400px]">
                                        <div class="val-primary truncate">{{ $item['judul_kegiatan'] }}</div>
                                        <div class="text-[10px] text-gray-400 font-medium uppercase tracking-tight">{{ $item['nama_organisasi'] }}</div>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <div class="val-primary text-xs">{{ $item['tanggal'] }}</div>
                                        <div class="text-[10px] text-gray-400 uppercase tracking-tighter">{{ \App\Models\Event::getBulanIndonesia($item['tanggal']) }}</div>
                                    </td>
                                    <td class="whitespace-nowrap">
                                        <div class="val-primary">{{ $item['lokasi'] }}</div>
                                        <div class="text-[10px] text-gray-400 font-bold uppercase tracking-tight">Lantai {{ $item['lantai'] }}</div>
                                    </td>
                                    <td>
                                        <span class="badge-status {{ $item['status_data'] === 'Valid' ? 'bg-emerald-50 text-emerald-600' : ($isHapus ? 'bg-rose-50 text-rose-600' : 'bg-amber-50 text-amber-600') }}">
                                            {{ $item['status_data'] }}
                                        </span>
                                    </td>
                                    <td>
                                        <div class="flex justify-center gap-1">
                                            <x-filament::icon-button 
                                                wire:click="deleteRow('{{ $item['id'] }}')" 
                                                icon="heroicon-o-trash" 
                                                color="danger" 
                                                size="sm"
                                                tooltip="Hapus Baris"
                                                wire:confirm="Hapus baris ini?"
                                            />
                                            <x-filament::icon-button 
                                                wire:click="validateRow('{{ $item['id'] }}')" 
                                                icon="heroicon-o-check" 
                                                color="success" 
                                                size="sm"
                                                tooltip="Validasi Baris"
                                            />
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Custom Pagination --}}
            <div class="flex items-center justify-between bg-white dark:bg-gray-900 px-6 py-4 rounded-2xl border border-gray-100 dark:border-gray-800 shadow-sm">
                <div class="text-xs font-bold text-gray-500 uppercase tracking-widest">
                    Grup <span class="text-gray-900 dark:text-white">{{ $currentPage }}</span> dari <span class="text-gray-900 dark:text-white">{{ count($duplikatGroups) }}</span>
                </div>
                
                <div class="flex items-center gap-2">
                    <x-filament::button 
                        wire:click="setPage({{ $currentPage - 1 }})"
                        :disabled="$currentPage <= 1"
                        color="gray"
                        size="sm"
                        icon="heroicon-o-chevron-left"
                        class="rounded-xl"
                    >
                        Prev
                    </x-filament::button>

                    <div class="flex items-center gap-1">
                        @php
                            $totalGroups = count($duplikatGroups);
                            $start = max(1, $currentPage - 2);
                            $end = min($totalGroups, $start + 4);
                            if ($end - $start < 4) $start = max(1, $end - 4);
                        @endphp
                        @for($i = max(1, $start); $i <= $end; $i++)
                            <button 
                                wire:click="setPage({{ $i }})"
                                class="w-8 h-8 rounded-lg text-xs font-black transition-all {{ $currentPage === $i ? 'bg-primary-600 text-white shadow-lg shadow-primary-500/30' : 'text-gray-500 hover:bg-gray-100 dark:hover:bg-gray-800' }}"
                            >
                                {{ $i }}
                            </button>
                        @endfor
                    </div>

                    <x-filament::button 
                        wire:click="setPage({{ $currentPage + 1 }})"
                        :disabled="$currentPage >= count($duplikatGroups)"
                        color="gray"
                        size="sm"
                        icon="heroicon-o-chevron-right"
                        icon-position="after"
                        class="rounded-xl"
                    >
                        Next
                    </x-filament::button>
                </div>
            </div>
        @else
            {{-- Empty State --}}
            <div class="flex flex-col items-center justify-center py-24 bg-white dark:bg-gray-900 rounded-3xl border border-dashed border-gray-300 dark:border-gray-700">
                <x-heroicon-o-check-circle class="w-16 h-16 text-emerald-500 mb-4" />
                <h3 class="text-lg font-bold text-gray-900 dark:text-white uppercase tracking-widest">Database Steril</h3>
                <p class="text-gray-400 text-sm mt-1">Tidak ditemukan duplikasi atau anomali data.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>