<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Filament\Forms\Get;
use App\Models\Event;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function mount(): void
    {
        // Pastikan filter year_start dan year_end selalu terinisialisasi
        if (empty($this->filters['year_start']) || empty($this->filters['year_end'])) {
            $latestYear = Event::valid()
                ->whereNotNull('tanggal')
                ->selectRaw('MAX(EXTRACT(YEAR FROM tanggal))::integer as max_year')
                ->value('max_year') ?? (int) date('Y');

            $currentFilters = $this->filters ?? [];
            $currentFilters['year_start'] = (string) $latestYear;
            $currentFilters['year_end']   = (string) $latestYear;
            $this->filters = $currentFilters;
        }
    }

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.dashboard.custom-styles');
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function filtersForm(Form $form): Form
    {
        // Ambil semua tahun yang tersedia dari data (tanpa cache agar selalu terupdate)
        $availableYears = Event::selectRaw('EXTRACT(YEAR FROM tanggal)::integer as year')
            ->valid()
            ->whereNotNull('tanggal')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->mapWithKeys(fn($v, $k) => [(string)$k => (string)$v])
            ->toArray();

        if (empty($availableYears)) {
            $latestYearDb = Event::valid()->whereNotNull('tanggal')->selectRaw('MAX(EXTRACT(YEAR FROM tanggal))::integer as max_year')->value('max_year') ?? date('Y');
            $availableYears = [(string)$latestYearDb => (string)$latestYearDb];
        }

        // Sub sektor (cache 30 menit, reactive ke tahun)
        $subSektors = function (Get $get) {
            $yearStart = $get('year_start');
            $yearEnd   = $get('year_end');
            
            $cacheKey = "dashboard_sub_sektors_{$yearStart}_{$yearEnd}";
            
            return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd) {
                return Event::valid()
                    ->byYearRange($yearStart, $yearEnd)
                    ->whereNotNull('sub_sektor')
                    ->where('sub_sektor', '!=', '')
                    ->distinct()
                    ->orderBy('sub_sektor')
                    ->pluck('sub_sektor', 'sub_sektor')
                    ->toArray();
            });
        };


        // Tahun terbaru sebagai default
        $latestYear = array_key_first($availableYears);

        // Semua nama bulan Indonesia
        $allMonths = [
            '01' => 'Januari',  '02' => 'Februari', '03' => 'Maret',
            '04' => 'April',    '05' => 'Mei',       '06' => 'Juni',
            '07' => 'Juli',     '08' => 'Agustus',   '09' => 'September',
            '10' => 'Oktober',  '11' => 'November',  '12' => 'Desember',
        ];

        return $form
            ->schema([
                Section::make()
                    ->compact()
                    ->schema([
                        Select::make('year_start')
                            ->label('Tahun Mulai')
                            ->options($availableYears)
                            ->default($latestYear)
                            ->selectablePlaceholder(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Jika year_start > year_end, sesuaikan year_end
                                $yearEnd = $get('year_end');
                                if ($state && $yearEnd && (int)$state > (int)$yearEnd) {
                                    $set('year_end', $state);
                                }
                                // Reset bulan saat tahun berubah
                                $set('month', null);
                            }),

                        Select::make('year_end')
                            ->label('Tahun Selesai')
                            ->options($availableYears)
                            ->default($latestYear)
                            ->selectablePlaceholder(false)
                            ->live()
                            ->afterStateUpdated(function ($state, $set, $get, $livewire) {
                                // Jika year_end < year_start, sesuaikan year_start
                                $yearStart = $get('year_start');
                                if ($state && $yearStart && (int)$state < (int)$yearStart) {
                                    $set('year_start', $state);
                                }
                                // Reset bulan saat tahun berubah
                                $set('month', null);
                            }),

                        Select::make('month')
                            ->label('Bulan')
                            ->options(function (Get $get) use ($allMonths) {
                                $yearStart = $get('year_start');
                                $yearEnd   = $get('year_end');
                                $subSektor = $get('sub_sektor');

                                if (!$yearStart || !$yearEnd || $yearStart !== $yearEnd) {
                                    return [];
                                }

                                $cacheKey = "dashboard_active_months_{$yearEnd}_" . md5($subSektor ?? 'all');

                                $activeMonths = Cache::remember(
                                    $cacheKey,
                                    now()->addMinutes(5),
                                    fn () => Event::valid()
                                        ->whereYear('tanggal', (int)$yearEnd)
                                        ->when($subSektor, fn($q) => $q->where('sub_sektor', $subSektor))
                                        ->selectRaw('EXTRACT(MONTH FROM tanggal)::integer as month_num')
                                        ->distinct()
                                        ->orderBy('month_num')
                                        ->pluck('month_num')
                                        ->toArray()
                                );

                                $options = [];
                                foreach ($allMonths as $key => $label) {
                                    if (in_array((int)$key, $activeMonths)) {
                                        $options[$key] = $label;
                                    }
                                }
                                return $options;
                            })
                            ->placeholder(function (Get $get) {
                                $yearStart = $get('year_start');
                                $yearEnd   = $get('year_end');

                                if (!$yearStart || !$yearEnd) return 'Pilih tahun dulu';
                                if ($yearStart !== $yearEnd) return 'N/A';
                                return 'Semua Bulan';
                            })
                            ->disabled(fn (Get $get) => !$get('year_start') || !$get('year_end') || $get('year_start') !== $get('year_end'))
                            ->live(),

                        Select::make('sub_sektor')
                            ->label('Kategori Ekraf')
                            ->placeholder('Semua Kategori')
                            ->options($subSektors)
                            ->live(),
                    ])
                    ->columns([
                        'default' => 1,
                        'sm' => 2,
                        'lg' => 4,
                    ]),


            ]);
    }
}
