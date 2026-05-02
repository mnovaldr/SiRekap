<x-filament-panels::page>
    <style>
        .integrity-card {
            background: #fff;
            border: 1px solid #e5e7eb;
            border-radius: 20px;
            padding: 24px;
            transition: all 0.3s ease;
        }
        .dark .integrity-card { background: #1f2937; border-color: #374151; }
        
        .stat-icon {
            width: 48px; height: 48px;
            border-radius: 14px;
            display: flex; align-items: center; justify-content: center;
        }

        .group-header {
            background: #f9fafb;
            border-radius: 24px 24px 0 0;
            padding: 20px 28px;
            border: 1px solid #e5e7eb;
            border-bottom: none;
        }
        .dark .group-header { background: #111827; border-color: #374151; }

        .item-card {
            background: #fff;
            border: 1px solid #f3f4f6;
            border-radius: 16px;
            padding: 16px;
            transition: all 0.2s;
        }
        .dark .item-card { background: #1f2937; border-color: #374151; }
        .item-card:hover { border-color: #d1d5db; transform: translateY(-2px); }

        .badge-pill {
            padding: 2px 10px;
            border-radius: 999px;
            font-size: 10px;
            font-weight: 800;
            text-transform: uppercase;
            letter-spacing: 0.05em;
        }
    </style>

    <div class="space-y-8">
        {{-- Header Section --}}
        <div class="flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <h1 class="text-3xl font-black tracking-tight text-gray-900 dark:text-white uppercase">Data Integrity</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400">Audit otomatis untuk duplikasi dan anomali lokasi.</p>
            </div>
            <x-filament::button wire:click="analyze" icon="heroicon-o-arrow-path" color="gray" class="rounded-xl shadow-sm">
                Re-Scan Database
            </x-filament::button>
        </div>

        {{-- Top Statistics --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div class="integrity-card flex items-center gap-6">
                <div class="stat-icon bg-rose-50 dark:bg-rose-900/20 text-rose-600">
                    <x-heroicon-o-document-duplicate class="w-7 h-7" />
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Duplikat Penuh</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $duplikatCount }}</p>
                </div>
            </div>

            <div class="integrity-card flex items-center gap-6">
                <div class="stat-icon bg-amber-50 dark:bg-amber-900/20 text-amber-600">
                    <x-heroicon-o-eye-slash class="w-7 h-7" />
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Perlu Review</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $reviewCount }}</p>
                </div>
            </div>

            <div class="integrity-card flex items-center gap-6 border-l-4 border-l-indigo-500">
                <div class="stat-icon bg-indigo-50 dark:bg-indigo-900/20 text-indigo-600">
                    <x-heroicon-o-shield-exclamation class="w-7 h-7" />
                </div>
                <div>
                    <p class="text-[10px] font-bold text-gray-400 uppercase tracking-widest">Total Isu</p>
                    <p class="text-3xl font-black text-gray-900 dark:text-white">{{ $totalIssues }}</p>
                </div>
            </div>
        </div>

        {{-- Main Analysis Content --}}
        @if(count($duplikatGroups) > 0)
            <div class="space-y-10">
                @foreach($duplikatGroups as $index => $group)
                    @php
                        $isHapus = str_contains($group['type'], 'Hapus');
                        $color = $isHapus ? 'rose' : 'amber';
                    @endphp
                    <div class="overflow-hidden">
                        <div class="group-header flex flex-wrap justify-between items-center gap-4 border-l-8 border-l-{{ $color }}-500">
                            <div class="flex items-center gap-4">
                                <div class="p-2 bg-{{ $color }}-100 dark:bg-{{ $color }}-900/30 rounded-lg text-{{ $color }}-600">
                                    <x-dynamic-component :component="$isHapus ? 'heroicon-o-trash' : 'heroicon-o-exclamation-triangle'" class="w-5 h-5" />
                                </div>
                                <div>
                                    <h3 class="text-sm font-black text-gray-900 dark:text-white uppercase tracking-tight">{{ $group['reason'] }}</h3>
                                    <p class="text-[10px] text-gray-500 font-mono italic">Fingerprint: {{ substr(md5($group['key']), 0, 12) }}</p>
                                </div>
                            </div>
                            
                            <x-filament::button 
                                wire:click="processGroup({{ $index }})" 
                                :color="$isHapus ? 'danger' : 'warning'" 
                                size="sm" 
                                icon="heroicon-o-sparkles"
                                class="rounded-lg shadow-sm"
                            >
                                {{ $isHapus ? 'Bersihkan Duplikat' : 'Validasi Semua' }}
                            </x-filament::button>
                        </div>

                        <div class="bg-gray-50/30 dark:bg-gray-900/20 border border-gray-200 dark:border-gray-800 p-6 rounded-b-3xl">
                            <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
                                @foreach($group['items'] as $item)
                                    <div class="item-card flex flex-col justify-between gap-4">
                                        <div class="flex justify-between items-start">
                                            <div class="space-y-1">
                                                <p class="text-[9px] font-black text-indigo-500 uppercase">{{ $item['nama_organisasi'] }}</p>
                                                <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200 leading-tight">{{ $item['judul_kegiatan'] }}</h4>
                                            </div>
                                            <div class="flex gap-1">
                                                <x-filament::icon-button 
                                                    wire:click="deleteRow('{{ $item['id'] }}')" 
                                                    icon="heroicon-o-trash" 
                                                    color="danger" 
                                                    size="sm"
                                                    tooltip="Hapus permanen"
                                                />
                                            </div>
                                        </div>

                                        <div class="grid grid-cols-2 gap-y-3 border-t border-gray-100 dark:border-gray-800 pt-3">
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-m-calendar class="w-3.5 h-3.5 text-gray-400" />
                                                <span class="text-[11px] font-medium text-gray-600 dark:text-gray-400">{{ $item['tanggal'] }}</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-m-map-pin class="w-3.5 h-3.5 text-gray-400" />
                                                <span class="text-[11px] font-medium text-gray-600 dark:text-gray-400 truncate">{{ $item['lokasi'] }} (Lt.{{ $item['lantai'] }})</span>
                                            </div>
                                            <div class="flex items-center gap-2">
                                                <x-heroicon-m-user-group class="w-3.5 h-3.5 text-gray-400" />
                                                <span class="text-[11px] font-medium text-gray-600 dark:text-gray-400">{{ number_format($item['jumlah_peserta']) }} Peserta</span>
                                            </div>
                                            <div>
                                                <span class="badge-pill {{ $item['status_data'] === 'Valid' ? 'bg-emerald-50 text-emerald-600' : 'bg-rose-50 text-rose-600' }}">
                                                    {{ $item['status_data'] }}
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            {{-- Perfect State --}}
            <div class="flex flex-col items-center justify-center py-32 integrity-card border-dashed">
                <div class="w-24 h-24 bg-emerald-50 dark:bg-emerald-900/20 rounded-full flex items-center justify-center mb-6">
                    <x-heroicon-o-check-badge class="w-12 h-12 text-emerald-600" />
                </div>
                <h3 class="text-xl font-black text-gray-900 dark:text-white uppercase tracking-tight">Database Bersih</h3>
                <p class="text-gray-500 text-sm mt-2">Tidak ditemukan anomali atau duplikasi data saat ini.</p>
            </div>
        @endif
    </div>
</x-filament-panels::page>