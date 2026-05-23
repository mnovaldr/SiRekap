<?php

namespace App\Filament\Widgets\Concerns;

use App\Models\Event;
use Illuminate\Support\Facades\Cache;

trait ResolvesYearFilter
{
    /**
     * Ambil tahun terbaru yang ada di database (selalu terupdate).
     */
    protected function getLatestDataYear(): int
    {
        return (int) (Event::valid()
            ->whereNotNull('tanggal')
            ->selectRaw('MAX(EXTRACT(YEAR FROM tanggal))::integer as max_year')
            ->value('max_year') ?? (int) date('Y'));
    }

    /**
     * Kembalikan nilai yearEnd yang efektif:
     * jika filter kosong, gunakan tahun terbaru dari database.
     */
    protected function getEffectiveYearEnd(): int
    {
        $yearEnd = $this->filters['year_end'] ?? null;
        return $yearEnd ? (int) $yearEnd : $this->getLatestDataYear();
    }

    /**
     * Kembalikan nilai yearStart yang efektif:
     * jika filter kosong, gunakan yearEnd (single-year mode).
     */
    protected function getEffectiveYearStart(): int
    {
        $yearStart = $this->filters['year_start'] ?? null;
        return $yearStart ? (int) $yearStart : $this->getEffectiveYearEnd();
    }

    /**
     * Kembalikan array [yearStart, yearEnd, month, subSektor] yang sudah resolved.
     */
    protected function resolveFilters(): array
    {
        $yearEnd   = $this->getEffectiveYearEnd();
        $yearStart = $this->getEffectiveYearStart();
        
        // Pastikan yearStart tidak lebih besar dari yearEnd
        if ($yearStart > $yearEnd) {
            $yearStart = $yearEnd;
        }
        
        $month = $this->filters['month'] ?? null;
        $subSektor = $this->filters['sub_sektor'] ?? null;

        return [$yearStart, $yearEnd, $month, $subSektor];
    }
}
