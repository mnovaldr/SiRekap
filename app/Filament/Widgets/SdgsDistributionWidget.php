<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\Cache;
use Livewire\WithPagination;

class SdgsDistributionWidget extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;
    use WithPagination;

    public ?string $selectedSdg = null;
    public string $sdgSearch = '';

    protected static string $view = 'filament.widgets.sdgs-distribution-widget';
    protected static ?int $sort = 9;
    protected int | string | array $columnSpan = 'full';
    protected static bool $isLazy = true;

    public function showEvents(string $sdgCode): void
    {
        $this->selectedSdg = $sdgCode;
        $this->sdgSearch = '';
        $this->resetPage('sdgPage');
    }

    public function updatedSdgSearch(): void
    {
        $this->resetPage('sdgPage');
    }

    public function resetSearch(): void
    {
        $this->sdgSearch = '';
        $this->resetPage('sdgPage');
    }

    public function closeEvents(): void
    {
        $this->selectedSdg = null;
    }

    public function getSdgEvents()
    {
        if (!$this->selectedSdg) {
            return null;
        }

        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode] = $this->resolveFilters();

        if (preg_match('/sdgs?\s*(\d{1,2})/i', $this->selectedSdg, $m)) {
            $num = (int)$m[1];
        } else {
            return null;
        }

        $query = Event::query()->valid();

        $query = $this->applyEventFilters($query);

        // Gunakan case-insensitive regex PostgreSQL dengan batas kata (\y)
        $query->whereRaw('program_sdgs ~* ?', ["(\\ySDGS?\\s*{$num}\\y|\\y{$num}\\y)"]);

        // Terapkan pencarian kata kunci pada nama_organisasi atau judul_kegiatan
        if (trim($this->sdgSearch) !== '') {
            $search = trim($this->sdgSearch);
            $query->where(function ($q) use ($search) {
                $q->where('nama_organisasi', 'ilike', '%' . $search . '%')
                  ->orWhere('judul_kegiatan', 'ilike', '%' . $search . '%');
            });
        }

        return $query->orderBy('tanggal', 'desc')->paginate(10, ['*'], 'sdgPage');
    }

    public function getSdgsData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode] = $this->resolveFilters();

        $cacheKey = "sdgs_distribution_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$bidang}_{$jenisKegiatan}_{$komersial}_{$sdg}_{$displayMode}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode) {
            $baseQuery = Event::query()->valid();

            $baseQuery = $this->applyEventFilters($baseQuery);



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
                // Split berdasarkan koma, titik koma, &, kata dan/and, atau lookahead untuk sdgs
                $parts = preg_split('/[;,&]|\bdan\b|\band\b|(?=\bsdgs?\s*\d)/i', $sdgsString);
                foreach ($parts as $part) {
                    $part = trim($part);
                    if ($part === '') {
                        continue;
                    }
                    // Cocokkan pola seperti "SDGS X", "SDG X", "sdgs X", atau hanya angka "X"
                    if (preg_match('/sdgs?\s*(\d{1,2})/i', $part, $m)) {
                        $num = (int)$m[1];
                        if ($num >= 1 && $num <= 17) {
                            $counts["SDGS $num"]++;
                        }
                    } elseif (preg_match('/^\d{1,2}$/', $part)) {
                        $num = (int)$part;
                        if ($num >= 1 && $num <= 17) {
                            $counts["SDGS $num"]++;
                        }
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
