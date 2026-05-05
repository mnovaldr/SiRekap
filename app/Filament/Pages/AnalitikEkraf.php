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
        return $form
            ->schema([
                Section::make()
                    ->schema([
                        Select::make('year')
                            ->label('Tahun Analisis')
                            ->options(
                                Event::selectRaw('EXTRACT(YEAR FROM tanggal) as year')
                                    ->distinct()
                                    ->orderBy('year', 'desc')
                                    ->pluck('year', 'year')
                                    ->toArray()
                            )
                            ->default(Event::selectRaw('EXTRACT(YEAR FROM MAX(tanggal)) as max_year')->value('max_year') ?? date('Y'))
                            ->selectablePlaceholder(false),
                    ])
                    ->columns(1),
            ]);
    }

    public function getAnalyticsData(): array
    {
        $year = $this->filters['year'] ?? date('Y');

        // Semua query hanya untuk data Valid
        $baseQuery = fn() => Event::whereYear('tanggal', $year)->where('status_data', 'Valid');

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

        // 4. Rekap Bulanan — hanya Valid, peserta hanya dari primary row
        $bulanan = $baseQuery()
            ->whereNotNull('bulan')
            ->select(
                'bulan',
                DB::raw('COUNT(CASE WHEN is_primary_row = true THEN 1 END) as total_event'),
                DB::raw('SUM(CASE WHEN is_primary_row = true THEN jumlah_peserta ELSE 0 END) as total_pengunjung'),
                DB::raw('COUNT(CASE WHEN is_primary_row = true AND komersial = \'Komersial\' THEN 1 END) as total_komersil'),
                DB::raw('COUNT(CASE WHEN is_primary_row = true AND komersial = \'Non Komersial\' THEN 1 END) as total_non_komersil'),
                DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as total_pelaku')
            )
            ->groupBy('bulan')
            ->orderBy(DB::raw('ARRAY_POSITION(ARRAY[\'Januari\',\'Februari\',\'Maret\',\'April\',\'Mei\',\'Juni\',\'Juli\',\'Agustus\',\'September\',\'Oktober\',\'November\',\'Desember\'], bulan)'))
            ->get();

        // 5. Total Pelaku (Sum of Monthly Unique Actors) sesuai permintaan user (4.039)
        $totalPelakuYear = $bulanan->sum('total_pelaku');

        // (Opsional) Jika ingin menampilkan angka unik murni (2.293) bisa gunakan ini:
        $pureUniquePelaku = $baseQuery()
            ->select(DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as total'))
            ->value('total') ?? 0;

        return [
            'kategoriEkraf'       => $kategoriEkraf,
            'kategoriKegiatan'    => $kategoriKegiatan,
            'kategoriPelaku'      => $kategoriPelaku,
            'bulanan'             => $bulanan,
            'totalPelakuYear'     => $totalPelakuYear,
            'pureUniquePelaku'    => $pureUniquePelaku,
            'year'                => $year,
        ];
    }
}
