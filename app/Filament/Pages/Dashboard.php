<?php

namespace App\Filament\Pages;

use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Pages\Dashboard\Concerns\HasFiltersForm;
use Filament\Forms\Components\Select;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class Dashboard extends BaseDashboard
{
    use HasFiltersForm;

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function filtersForm(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                Select::make('year')
                    ->label('Tahun')
                    ->options(function () {
                        $years = Event::selectRaw('YEAR(tanggal) as year')
                            ->whereNotNull('tanggal')
                            ->distinct()
                            ->orderBy('year', 'desc')
                            ->pluck('year', 'year')
                            ->toArray();
                        
                        return count($years) > 0 ? $years : [date('Y') => date('Y')];
                    })
                    ->placeholder('Semua Tahun')
                    ->default(function () {
                        $latestYear = Event::whereNotNull('tanggal')
                            ->orderBy('tanggal', 'desc')
                            ->value(DB::raw('YEAR(tanggal)'));
                        
                        return $latestYear ?: date('Y');
                    }),
                
                Select::make('month')
                    ->label('Bulan')
                    ->options([
                        '01' => 'Januari', '02' => 'Februari', '03' => 'Maret', 
                        '04' => 'April', '05' => 'Mei', '06' => 'Juni', 
                        '07' => 'Juli', '08' => 'Agustus', '09' => 'September', 
                        '10' => 'Oktober', '11' => 'November', '12' => 'Desember'
                    ])
                    ->placeholder('Semua Bulan'),
            ])
            ->columns(2);
    }
}
