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
                                Event::selectRaw('YEAR(tanggal) as year')
                                    ->distinct()
                                    ->orderBy('year', 'desc')
                                    ->pluck('year', 'year')
                                    ->toArray()
                            )
                            ->default(Event::selectRaw('YEAR(MAX(tanggal)) as max_year')->value('max_year') ?? date('Y'))
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
                DB::raw('count(*) as total_event'),
                // SUM peserta hanya dari is_primary_row=1 agar tidak double-count multi-ruangan
                DB::raw('sum(case when is_primary_row = 1 then jumlah_peserta else 0 end) as total_pengunjung'),
                DB::raw('count(case when komersial = "Komersial" then 1 end) as total_komersil'),
                DB::raw('count(case when komersial = "Non Komersial" then 1 end) as total_non_komersil'),
                DB::raw('count(distinct nama_organisasi) as total_pelaku')
            )
            ->groupBy('bulan')
            ->orderBy(DB::raw('FIELD(bulan, "Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember")'))
            ->get();

        // 5. Organisasi Unik lifetime (hanya Valid)
        $totalPelakuLifetime = Event::valid()
            ->count(DB::raw('DISTINCT nama_organisasi'));

        return [
            'kategoriEkraf'       => $kategoriEkraf,
            'kategoriKegiatan'    => $kategoriKegiatan,
            'kategoriPelaku'      => $kategoriPelaku,
            'bulanan'             => $bulanan,
            'totalPelakuLifetime' => $totalPelakuLifetime,
            'year'                => $year,
        ];
    }
}
