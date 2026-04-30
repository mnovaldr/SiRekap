<div class="space-y-4 text-sm">
    {{-- Header Info --}}
    <div class="grid grid-cols-2 gap-4">
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Waktu</p>
            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                {{ $record->created_at->timezone('Asia/Jakarta')->format('d M Y, H:i:s') }} WIB
            </p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Pengguna</p>
            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                {{ $record->user?->name ?? 'System' }}
            </p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Jenis Aksi</p>
            <p class="mt-1">
                <span class="inline-flex items-center gap-1.5 px-2.5 py-1 rounded-full text-xs font-semibold
                    {{ match($record->action_color) {
                        'success' => 'bg-green-100 text-green-800 dark:bg-green-900 dark:text-green-200',
                        'danger'  => 'bg-red-100 text-red-800 dark:bg-red-900 dark:text-red-200',
                        'info'    => 'bg-blue-100 text-blue-800 dark:bg-blue-900 dark:text-blue-200',
                        'warning' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900 dark:text-yellow-200',
                        'primary' => 'bg-indigo-100 text-indigo-800 dark:bg-indigo-900 dark:text-indigo-200',
                        default   => 'bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-200',
                    } }}">
                    {{ $record->action_label }}
                </span>
            </p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Objek</p>
            <p class="mt-1 font-semibold text-gray-900 dark:text-white">
                {{ $record->model_type ?? '-' }}
                @if($record->model_id)
                    <span class="text-xs text-gray-400 font-normal ml-1">#{{ $record->model_id }}</span>
                @endif
            </p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">IP Address</p>
            <p class="mt-1 font-mono text-gray-900 dark:text-white">{{ $record->ip_address ?? '-' }}</p>
        </div>
        <div>
            <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Browser</p>
            <p class="mt-1 text-gray-700 dark:text-gray-300 truncate" title="{{ $record->user_agent }}">
                {{ $record->user_agent ? Str::limit($record->user_agent, 60) : '-' }}
            </p>
        </div>
    </div>

    {{-- Keterangan --}}
    @if($record->description)
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-2">Keterangan</p>
        <p class="text-gray-900 dark:text-white">{{ $record->description }}</p>
    </div>
    @endif

    {{-- Properties --}}
    @if($record->properties)
    <div class="border-t border-gray-200 dark:border-gray-700 pt-4">
        <p class="text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Detail Perubahan</p>

        @if(isset($record->properties['before']) && isset($record->properties['after']))
            {{-- Before / After diff --}}
            <div class="grid grid-cols-2 gap-3">
                <div>
                    <p class="text-xs font-semibold text-red-600 dark:text-red-400 mb-2">Sebelum</p>
                    <div class="bg-red-50 dark:bg-red-900/20 rounded-lg p-3 space-y-1">
                        @foreach($record->properties['before'] as $key => $val)
                        <div class="flex gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-28 shrink-0">{{ $key }}</span>
                            <span class="text-xs font-medium text-gray-900 dark:text-white break-all">
                                {{ is_array($val) ? json_encode($val) : ($val ?? '-') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                <div>
                    <p class="text-xs font-semibold text-green-600 dark:text-green-400 mb-2">Sesudah</p>
                    <div class="bg-green-50 dark:bg-green-900/20 rounded-lg p-3 space-y-1">
                        @foreach($record->properties['after'] as $key => $val)
                        <div class="flex gap-2">
                            <span class="text-xs text-gray-500 dark:text-gray-400 w-28 shrink-0">{{ $key }}</span>
                            <span class="text-xs font-medium text-gray-900 dark:text-white break-all">
                                {{ is_array($val) ? json_encode($val) : ($val ?? '-') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @else
            {{-- Generic properties --}}
            <div class="bg-gray-50 dark:bg-gray-800 rounded-lg p-3 space-y-1">
                @foreach($record->properties as $key => $val)
                @if(!is_array($val))
                <div class="flex gap-2">
                    <span class="text-xs text-gray-500 dark:text-gray-400 w-32 shrink-0">{{ $key }}</span>
                    <span class="text-xs font-medium text-gray-900 dark:text-white break-all">{{ $val ?? '-' }}</span>
                </div>
                @else
                <div>
                    <p class="text-xs text-gray-500 dark:text-gray-400 mb-1">{{ $key }}</p>
                    <div class="pl-3 space-y-1">
                        @foreach($val as $k => $v)
                        <div class="flex gap-2">
                            <span class="text-xs text-gray-400 w-28 shrink-0">{{ $k }}</span>
                            <span class="text-xs text-gray-700 dark:text-gray-300 break-all">
                                {{ is_array($v) ? json_encode($v) : ($v ?? '-') }}
                            </span>
                        </div>
                        @endforeach
                    </div>
                </div>
                @endif
                @endforeach
            </div>
        @endif
    </div>
    @endif
</div>
