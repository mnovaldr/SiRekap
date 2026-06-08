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
        $bidang = $this->filters['bidang'] ?? null;
        $jenisKegiatan = $this->filters['jenis_kegiatan'] ?? null;
        $komersial = $this->filters['komersial'] ?? null;
        $sdg = $this->filters['sdg'] ?? null;

        return [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, 'unique'];
    }

    protected function applyEventFilters($query, array $exclude = []): \Illuminate\Database\Eloquent\Builder
    {
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg] = $this->resolveFilters();

        return $query
            ->when(!in_array('year', $exclude), fn($q) => $q->byYearRange($yearStart, $yearEnd))
            ->when(!in_array('month', $exclude) && $month, fn($q) => $q->whereMonth('tanggal', $month))
            ->when(!in_array('sub_sektor', $exclude) && $subSektor, fn($q) => $q->bySubSektor($subSektor))
            ->when(!in_array('bidang', $exclude) && $bidang, fn($q) => $q->byBidang($bidang))
            ->when(!in_array('jenis_kegiatan', $exclude) && $jenisKegiatan, fn($q) => $q->byJenisKegiatan($jenisKegiatan))
            ->when(!in_array('komersial', $exclude) && $komersial, fn($q) => $q->byKomersial($komersial))
            ->when(!in_array('sdg', $exclude) && $sdg, fn($q) => $q->bySdg($sdg))
            ->when(!in_array('primary', $exclude), fn($q) => $q->primary());
    }


}

