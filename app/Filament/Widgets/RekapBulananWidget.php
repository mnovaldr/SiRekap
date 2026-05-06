<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;

class RekapBulananWidget extends BaseWidget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static ?string $heading = 'Rekapitulasi Performa Bulanan';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 8;
    protected static bool $isLazy = true;



    public function getTableRecordKey($record): string
    {
        return (string) ($record->bulan ?? 'unknown');
    }

    public function table(Table $table): Table
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        return $table
            ->query(
                Event::select(
                    'bulan',
                    DB::raw('COUNT(CASE WHEN is_primary_row = true THEN 1 END) as total_event'),
                    DB::raw('SUM(CASE WHEN is_primary_row = true THEN jumlah_peserta ELSE 0 END) as total_peserta'),
                    DB::raw('COUNT(DISTINCT CASE WHEN nama_organisasi IS NOT NULL AND nama_organisasi != \'\' THEN UPPER(TRIM(nama_organisasi)) END) as total_organisasi')
                )
                ->valid()
                ->byYearRange($yearStart, $yearEnd)
                ->bySubSektor($subSektor)
                ->whereNotNull('bulan')
                ->groupBy('bulan')
                ->orderBy(DB::raw('ARRAY_POSITION(ARRAY[\'Januari\', \'Februari\', \'Maret\', \'April\', \'Mei\', \'Juni\', \'Juli\', \'Agustus\', \'September\', \'Oktober\', \'November\', \'Desember\'], bulan)'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('bulan')
                    ->label('Bulan')
                    ->weight('bold')
                    ->color('gray')
                    ->icon('heroicon-m-calendar-days')
                    ->iconColor('gray'),

                Tables\Columns\TextColumn::make('total_event')
                    ->label('Jumlah Event')
                    ->numeric()
                    ->badge()
                    ->color('indigo')
                    ->weight('bold')
                    ->alignCenter()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),

                Tables\Columns\TextColumn::make('total_peserta')
                    ->label('Total Pengunjung')
                    ->numeric()
                    ->weight('bold')
                    ->color('emerald')
                    ->icon('heroicon-m-users')
                    ->alignCenter()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),

                Tables\Columns\TextColumn::make('total_organisasi')
                    ->label('Organisasi Unik')
                    ->numeric()
                    ->badge()
                    ->color('purple')
                    ->weight('bold')
                    ->alignCenter()
                    ->summarize(Tables\Columns\Summarizers\Sum::make()->label('Total')),
            ])
            ->paginated(false)
            ->striped()
            ->contentGrid([
                'md' => 1,
            ]);
    }
}
