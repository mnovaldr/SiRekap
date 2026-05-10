<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;


class EventsChart extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static bool $isLazy = false;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.events-chart';

    public function getChartData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        $cacheKey = "events_chart_v4_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor) {
            // ── DAILY view (month selected) ─────────────────────────────────
            if ($month) {
                $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $month, (int) $yearEnd);

                $eventData = Event::valid()
                    ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [(int)$yearEnd])
                    ->whereRaw('EXTRACT(MONTH FROM "tanggal") = ?', [(int)$month])
                    ->bySubSektor($subSektor)
                    ->primary()
                    ->selectRaw('EXTRACT(DAY FROM "tanggal")::integer as d, count(*) as cnt, sum(jumlah_peserta) as peserta,
                        COUNT(CASE WHEN "komersial" = \'Komersial\' THEN 1 END) as komersil,
                        COUNT(CASE WHEN "komersial" = \'Non Komersial\' THEN 1 END) as non_komersil')
                    ->groupBy(DB::raw('EXTRACT(DAY FROM "tanggal")'))
                    ->get()
                    ->mapWithKeys(fn($item) => [(int)$item->d => $item]);

                $labels = $events = $peserta = $komersil = $nonKom = [];
                for ($i = 1; $i <= $daysInMonth; $i++) {
                    $labels[]   = (string) $i;
                    $row        = $eventData->get($i);
                    $events[]   = (int) ($row?->cnt          ?? 0);
                    $peserta[]  = (int) ($row?->peserta      ?? 0);
                    $komersil[] = (int) ($row?->komersil     ?? 0);
                    $nonKom[]   = (int) ($row?->non_komersil ?? 0);
                }

                $title    = 'Trend Harian — ' . Event::getBulanIndonesia($month) . ' ' . $yearEnd;
                if ($subSektor) $title .= " ($subSektor)";
                $subtitle = 'Event & Peserta per hari';

            // ── ANNUAL range view ────────────────────────────────────────────
            } elseif ($yearStart != $yearEnd) {
                $rows = Event::valid()
                    ->primary()
                    ->bySubSektor($subSektor)
                    ->selectRaw('EXTRACT(YEAR FROM "tanggal")::integer as yr,
                        count(*) as cnt, sum(jumlah_peserta) as peserta,
                        COUNT(CASE WHEN "komersial" = \'Komersial\' THEN 1 END) as komersil,
                        COUNT(CASE WHEN "komersial" = \'Non Komersial\' THEN 1 END) as non_komersil')
                    ->whereRaw('EXTRACT(YEAR FROM "tanggal") BETWEEN ? AND ?', [(int)$yearStart, (int)$yearEnd])
                    ->groupBy(DB::raw('EXTRACT(YEAR FROM "tanggal")'))
                    ->get()
                    ->mapWithKeys(fn($item) => [(int)$item->yr => $item]);

                $labels = $events = $peserta = $komersil = $nonKom = [];
                foreach (range($yearStart, $yearEnd) as $yr) {
                    $labels[]   = (string) $yr;
                    $row        = $rows->get((int)$yr);
                    $events[]   = (int) ($row?->cnt          ?? 0);
                    $peserta[]  = (int) ($row?->peserta      ?? 0);
                    $komersil[] = (int) ($row?->komersil     ?? 0);
                    $nonKom[]   = (int) ($row?->non_komersil ?? 0);
                }

                $title    = "Trend Tahunan ($yearStart – $yearEnd)";
                if ($subSektor) $title .= " — $subSektor";
                $subtitle = 'Event & Peserta per tahun';

            // ── MONTHLY view (single year) ───────────────────────────────────
            } else {
                $rows = Event::valid()
                    ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [(int)$yearEnd])
                    ->bySubSektor($subSektor)
                    ->primary()
                    ->selectRaw('EXTRACT(MONTH FROM "tanggal")::integer as mo,
                        count(*) as cnt, sum(jumlah_peserta) as peserta,
                        COUNT(CASE WHEN "komersial" = \'Komersial\' THEN 1 END) as komersil,
                        COUNT(CASE WHEN "komersial" = \'Non Komersial\' THEN 1 END) as non_komersil')
                    ->groupBy(DB::raw('EXTRACT(MONTH FROM "tanggal")'))
                    ->get()
                    ->mapWithKeys(fn($item) => [(int)$item->mo => $item]);

                $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
                $labels = $events = $peserta = $komersil = $nonKom = [];
                for ($i = 1; $i <= 12; $i++) {
                    $labels[]   = $monthNames[$i - 1];
                    $row        = $rows->get($i);
                    $events[]   = (int) ($row?->cnt          ?? 0);
                    $peserta[]  = (int) ($row?->peserta      ?? 0);
                    $komersil[] = (int) ($row?->komersil     ?? 0);
                    $nonKom[]   = (int) ($row?->non_komersil ?? 0);
                }

                $title    = "Trend Aktivitas Bulanan $yearEnd";
                if ($subSektor) $title .= " — $subSektor";
                $subtitle = 'Event & Peserta per bulan — ' . $yearEnd;
            }

            return compact('labels', 'events', 'peserta', 'komersil', 'nonKom', 'title', 'subtitle');
        });
    }
}
