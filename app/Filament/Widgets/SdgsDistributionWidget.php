<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\Cache;

class SdgsDistributionWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static string $view = 'filament.widgets.sdgs-distribution-widget';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = true;

    public function getSdgsData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $cacheKey = "sdgs_distribution_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor) {
            $baseQuery = Event::query()->valid()
                ->primary()
                ->byYearRange($yearStart, $yearEnd)
                ->bySubSektor($subSektor);

            if ($month) {
                $baseQuery->whereMonth('tanggal', $month);
            }

            // Total event primary yang sesuai filter
            $totalEvents = $baseQuery->count();

            // Ambil semua kolom program_sdgs yang tidak kosong
            $sdgsList = (clone $baseQuery)
                ->whereNotNull('program_sdgs')
                ->where('program_sdgs', '!=', '')
                ->pluck('program_sdgs');

            // Inisialisasi hitungan untuk 17 program SDGs
            $counts = [];
            for ($i = 1; $i <= 17; $i++) {
                $counts["SDGS $i"] = 0;
            }

            foreach ($sdgsList as $sdgsString) {
                $parts = explode(';', $sdgsString);
                foreach ($parts as $part) {
                    $part = trim($part);
                    // Sesuaikan format jika ada variasi "SDGS X" vs "SDGS Y"
                    if (isset($counts[$part])) {
                        $counts[$part]++;
                    }
                }
            }

            return [
                'counts' => $counts,
                'totalEvents' => $totalEvents,
                'totalWithSdgs' => count($sdgsList),
            ];
        });
    }
}
