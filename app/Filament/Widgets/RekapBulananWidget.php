<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use Illuminate\Support\Facades\DB;

class RekapBulananWidget extends BaseWidget
{
    use InteractsWithPageFilters;

    protected static ?string $heading = 'Rekapitulasi Performa Bulanan';
    protected int | string | array $columnSpan = 'full';
    protected static ?int $sort = 8;

    protected function getListeners(): array
    {
        return [
            'updateFilter' => '$refresh',
        ];
    }

    public function getTableRecordKey($record): string
    {
        return (string) ($record->bulan ?? 'unknown');
    }

    public function table(Table $table): Table
    {
        $year = $this->filters['year'] ?? null;

        return $table
            ->query(
                Event::select(
                    'bulan',
                    DB::raw('count(*) as total_event'),
                    DB::raw('sum(jumlah_peserta) as total_peserta'),
                    DB::raw('count(distinct nama_organisasi) as total_organisasi')
                )
                ->valid()
                ->byYear($year)
                ->whereNotNull('bulan')
                ->groupBy('bulan')
                ->orderBy(DB::raw('FIELD(bulan, "Januari", "Februari", "Maret", "April", "Mei", "Juni", "Juli", "Agustus", "September", "Oktober", "November", "Desember")'))
            )
            ->columns([
                Tables\Columns\TextColumn::make('bulan')
                    ->label('Periode')
                    ->size(Tables\Columns\TextColumn\TextColumnSize::Medium)
                    ->weight('black')
                    ->color('primary'),
                Tables\Columns\TextColumn::make('total_event')
                    ->label('Jumlah Event')
                    ->numeric()
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_peserta')
                    ->label('Total Pengunjung')
                    ->numeric()
                    ->weight('bold')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('total_organisasi')
                    ->label('Organisasi Unik')
                    ->numeric()
                    ->badge()
                    ->color('success')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}
