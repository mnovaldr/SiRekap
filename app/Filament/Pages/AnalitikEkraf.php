<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use App\Models\Event;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Section;
use Filament\Forms\Form;
use Illuminate\Support\Facades\DB;

class AnalitikEkraf extends Page
{
    use HasFiltersForm;

    protected static ?string $navigationIcon = 'heroicon-o-chart-bar';
    protected static string $view = 'filament.pages.analitik-ekraf';
    protected static ?string $navigationLabel = 'Analitik Ekraf';
    protected static ?string $navigationGroup = 'Laporan';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Analitik Ekonomi Kreatif';

    public function filtersForm(Form $form): Form
    {
        $availableYears = Event::selectRaw('EXTRACT(YEAR FROM "tanggal")::integer as year')
            ->whereNotNull('tanggal')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year', 'year')
            ->mapWithKeys(fn ($v, $k) => [(string) $k => (string) $v])
            ->toArray();

        $latestYear = array_key_first($availableYears) ?? (string) date('Y');

        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('year')
                            ->label('Tahun Analisis')
                            ->options($availableYears)
                            ->default($latestYear)
                            ->selectablePlaceholder(false)
                            ->live(),
                    ])
                    ->columns(1),
            ]);
    }

    public function getAnalyticsData(): array
    {
        $year = $this->filters['year'] ?? null;

        if (empty($year)) {
            $availableYears = Event::selectRaw('EXTRACT(YEAR FROM "tanggal")::integer as year')
                ->whereNotNull('tanggal')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year', 'year')
                ->mapWithKeys(fn ($v, $k) => [(string) $k => (string) $v])
                ->toArray();
            $year = array_key_first($availableYears) ?? date('Y');
        }

        $cacheKey = "analitik_data_{$year}_v3";

        return \Illuminate\Support\Facades\Cache::remember($cacheKey, now()->addMinutes(15), function () use ($year) {
            return $this->fetchAnalyticsData($year);
        });
    }

    private function fetchAnalyticsData(int|string $year): array
    {
        // Semua query hanya untuk data Valid
        $baseQuery = fn() => Event::whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [(int)$year])->valid();

        // 1. Sub Sektor
        $kategoriEkraf = $baseQuery()
            ->whereNotNull('sub_sektor')
            ->where('sub_sektor', '!=', '')
            ->select('sub_sektor as label', DB::raw('count(*) as total'))
            ->groupBy('sub_sektor')
            ->orderBy('total', 'desc')
            ->get();

        // 2. Jenis Kegiatan
        $kategoriKegiatan = $baseQuery()
            ->select('jenis_kegiatan as label', DB::raw('count(*) as total'))
            ->groupBy('jenis_kegiatan')
            ->orderBy('total', 'desc')
            ->get();

        // 3. Bidang Pelaku
        $kategoriPelaku = $baseQuery()
            ->select('bidang as label', DB::raw('count(*) as total'))
            ->groupBy('bidang')
            ->orderBy('total', 'desc')
            ->get();

        // 4. Rekap Bulanan — Ambil bulan dari tanggal sebagai fallback utama
        $bulanan = $baseQuery()
            ->select(
                DB::raw('EXTRACT(MONTH FROM "tanggal") as month_num'),
                DB::raw('COUNT(CASE WHEN "is_primary_row" = true THEN 1 END) as total_event'),
                DB::raw('SUM(CASE WHEN "is_primary_row" = true THEN "jumlah_peserta" ELSE 0 END) as total_pengunjung'),
                DB::raw('COUNT(CASE WHEN "is_primary_row" = true AND "komersial" = \'Komersial\' THEN 1 END) as total_komersil'),
                DB::raw('COUNT(CASE WHEN "is_primary_row" = true AND "komersial" = \'Non Komersial\' THEN 1 END) as total_non_komersil'),
                DB::raw('COUNT(DISTINCT CASE WHEN "nama_organisasi" IS NOT NULL AND "nama_organisasi" != \'\' THEN "nama_organisasi" END) as total_pelaku')
            )
            ->groupBy('month_num')
            ->orderBy('month_num')
            ->get()
            ->map(function ($item) {
                $months = [
                    1 => 'Januari', 2 => 'Februari', 3 => 'Maret', 4 => 'April', 5 => 'Mei', 6 => 'Juni',
                    7 => 'Juli', 8 => 'Agustus', 9 => 'September', 10 => 'Oktober', 11 => 'November', 12 => 'Desember'
                ];
                $item->bulan = $months[(int)$item->month_num] ?? 'Unknown';
                return $item;
            });

        // 5. Total Pelaku (Sum of Monthly Unique Actors) sesuai permintaan user (4.039)
        $totalPelakuYear = $bulanan->sum('total_pelaku');

        // (Opsional) Jika ingin menampilkan angka unik murni (2.293) bisa gunakan ini:
        $pureUniquePelaku = $baseQuery()
            ->select(DB::raw('COUNT(DISTINCT CASE WHEN "nama_organisasi" IS NOT NULL AND "nama_organisasi" != \'\' THEN "nama_organisasi" END) as total'))
            ->value('total') ?? 0;

        // 6. SDGs Counts (Primary Events only for consistency)
        $sdgsList = $baseQuery()
            ->primary()
            ->whereNotNull('program_sdgs')
            ->where('program_sdgs', '!=', '')
            ->pluck('program_sdgs');

        $sdgsCounts = [];
        for ($i = 1; $i <= 17; $i++) {
            $sdgsCounts["SDGS $i"] = 0;
        }

        foreach ($sdgsList as $sdgsString) {
            $parts = preg_split('/[;,&]|\bdan\b|\band\b|(?=\bsdgs?\s*\d)/i', $sdgsString);
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') {
                    continue;
                }
                if (preg_match('/sdgs?\s*(\d{1,2})/i', $part, $m)) {
                    $num = (int)$m[1];
                    if ($num >= 1 && $num <= 17) {
                        $sdgsCounts["SDGS $num"]++;
                    }
                } elseif (preg_match('/^\d{1,2}$/', $part)) {
                    $num = (int)$part;
                    if ($num >= 1 && $num <= 17) {
                        $sdgsCounts["SDGS $num"]++;
                    }
                }
            }
        }

        return [
            'kategoriEkraf'       => $kategoriEkraf,
            'kategoriKegiatan'    => $kategoriKegiatan,
            'kategoriPelaku'      => $kategoriPelaku,
            'bulanan'             => $bulanan,
            'totalPelakuYear'     => $totalPelakuYear,
            'pureUniquePelaku'    => $pureUniquePelaku,
            'sdgsCounts'          => $sdgsCounts,
            'year'                => $year,
        ];
    }
}
