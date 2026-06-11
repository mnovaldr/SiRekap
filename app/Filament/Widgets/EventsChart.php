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

    protected static bool $isLazy = true;

    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.events-chart';

    public function getChartData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode] = $this->resolveFilters();

        $cacheKey = "events_chart_v4_{$yearStart}_{$yearEnd}_{$month}_{$subSektor}_{$bidang}_{$jenisKegiatan}_{$komersial}_{$sdg}_{$displayMode}";

        return Cache::remember($cacheKey, now()->addMinutes(10), function () use ($yearStart, $yearEnd, $month, $subSektor, $bidang, $jenisKegiatan, $komersial, $sdg, $displayMode) {
            // ── DAILY view (month selected) ─────────────────────────────────
            if ($month) {
                $daysInMonth = (int) date('t', strtotime("{$yearEnd}-{$month}-01"));

                $query = Event::valid()
                    ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [(int)$yearEnd])
                    ->whereRaw('EXTRACT(MONTH FROM "tanggal") = ?', [(int)$month]);

                $query = $this->applyEventFilters($query, ['year', 'month']);

                $eventData = $query->selectRaw('EXTRACT(DAY FROM "tanggal")::integer as d, count(*) as cnt, sum(jumlah_peserta) as peserta,
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
                if ($bidang) $title .= " ($bidang)";
                if ($jenisKegiatan) $title .= " ($jenisKegiatan)";
                if ($komersial) $title .= " ($komersial)";
                if ($sdg) $title .= " ($sdg)";
                $subtitle = 'Event & Peserta per hari';

            // ── ANNUAL range view ────────────────────────────────────────────
            } elseif ($yearStart != $yearEnd) {
                $query = Event::valid();

                $query = $this->applyEventFilters($query, ['year', 'month']);

                $rows = $query->selectRaw('EXTRACT(YEAR FROM "tanggal")::integer as yr,
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
                if ($bidang) $title .= " — $bidang";
                if ($jenisKegiatan) $title .= " — $jenisKegiatan";
                if ($komersial) $title .= " — $komersial";
                if ($sdg) $title .= " — $sdg";
                $subtitle = 'Event & Peserta per tahun';

            // ── MONTHLY view (single year) ───────────────────────────────────
            } else {
                $query = Event::valid()
                    ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [(int)$yearEnd]);

                $query = $this->applyEventFilters($query, ['year', 'month']);

                $rows = $query->selectRaw('EXTRACT(MONTH FROM "tanggal")::integer as mo,
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
                if ($bidang) $title .= " — $bidang";
                if ($jenisKegiatan) $title .= " — $jenisKegiatan";
                if ($komersial) $title .= " — $komersial";
                if ($sdg) $title .= " — $sdg";
                $subtitle = 'Event & Peserta per bulan — ' . $yearEnd;
            }


            return compact('labels', 'events', 'peserta', 'komersil', 'nonKom', 'title', 'subtitle');
        });
    }
}
