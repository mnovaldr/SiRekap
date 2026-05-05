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

    public function getHeader(): ?\Illuminate\Contracts\View\View
    {
        return view('filament.dashboard.custom-styles');
    }

    public function getColumns(): int | string | array
    {
        return 2;
    }

    public function filtersForm(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        $yearOptions = function () {
            $years = Event::selectRaw('EXTRACT(YEAR FROM tanggal) as year')
                ->whereNotNull('tanggal')
                ->distinct()
                ->orderBy('year', 'desc')
                ->pluck('year', 'year')
                ->toArray();
            
            return count($years) > 0 ? $years : [date('Y') => date('Y')];
        };

        $defaultYear = function () {
            $latestYear = Event::whereNotNull('tanggal')
                ->orderBy('tanggal', 'desc')
                ->value(DB::raw('EXTRACT(YEAR FROM tanggal)'));
            
            return (string) ($latestYear ?: date('Y'));
        };

        return $form
            ->schema([
                Select::make('year_start')
                    ->label('Tahun Mulai')
                    ->options($yearOptions)
                    ->default($defaultYear)
                    ->required(),
                
                Select::make('year_end')
                    ->label('Tahun Selesai')
                    ->options($yearOptions)
                    ->default($defaultYear)
                    ->required(),

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
            ->columns(3);
    }
}
