<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Widgets\Widget;
use Filament\Widgets\Concerns\InteractsWithPageFilters;
use App\Filament\Widgets\Concerns\ResolvesYearFilter;
use Illuminate\Support\Facades\DB;

class EventsChart extends Widget
{
    use InteractsWithPageFilters;
    use ResolvesYearFilter;

    protected static bool $isLazy = false;
    protected static ?int $sort = 2;
    protected int | string | array $columnSpan = 'full';
    protected static string $view = 'filament.widgets.events-chart';

    protected function getListeners(): array
    {
        return [
            'updateFilter' => '$refresh',
        ];
    }

    public function getChartData(): array
    {
        [$yearStart, $yearEnd, $month, $subSektor] = $this->resolveFilters();

        // ── DAILY view (month selected) ─────────────────────────────────
        if ($month) {
            $daysInMonth = cal_days_in_month(CAL_GREGORIAN, (int) $month, $yearEnd);

            $eventData = Event::valid()
                ->byYear($yearEnd)
                ->byMonth($month)
                ->bySubSektor($subSektor)
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(DAY FROM tanggal)::integer as d, count(*) as cnt, sum(jumlah_peserta) as peserta,
                    COUNT(CASE WHEN komersial = \'Komersial\' THEN 1 END) as komersil,
                    COUNT(CASE WHEN komersial = \'Non Komersial\' THEN 1 END) as non_komersil')
                ->groupBy(DB::raw('EXTRACT(DAY FROM tanggal)'))
                ->get()->keyBy('d');

            $labels = $events = $peserta = $komersil = $nonKom = [];
            for ($i = 1; $i <= $daysInMonth; $i++) {
                $labels[]   = (string) $i;
                $events[]   = (int) ($eventData[$i]->cnt          ?? 0);
                $peserta[]  = (int) ($eventData[$i]->peserta      ?? 0);
                $komersil[] = (int) ($eventData[$i]->komersil     ?? 0);
                $nonKom[]   = (int) ($eventData[$i]->non_komersil ?? 0);
            }

            $title    = 'Trend Harian — ' . Event::getBulanIndonesia($month) . ' ' . $yearEnd;
            if ($subSektor) $title .= " ($subSektor)";
            $subtitle = 'Event & Peserta per hari';

        // ── ANNUAL range view ────────────────────────────────────────────
        } elseif ($yearStart !== $yearEnd) {
            $rows = Event::valid()
                ->where('is_primary_row', true)
                ->bySubSektor($subSektor)
                ->selectRaw('EXTRACT(YEAR FROM tanggal)::integer as yr,
                    count(*) as cnt, sum(jumlah_peserta) as peserta,
                    COUNT(CASE WHEN komersial = \'Komersial\' THEN 1 END) as komersil,
                    COUNT(CASE WHEN komersial = \'Non Komersial\' THEN 1 END) as non_komersil')
                ->whereRaw('EXTRACT(YEAR FROM tanggal) BETWEEN ? AND ?', [$yearStart, $yearEnd])
                ->groupBy(DB::raw('EXTRACT(YEAR FROM tanggal)'))
                ->get()->keyBy('yr');

            $labels = $events = $peserta = $komersil = $nonKom = [];
            foreach (range($yearStart, $yearEnd) as $yr) {
                $labels[]   = (string) $yr;
                $events[]   = (int) ($rows[$yr]->cnt          ?? 0);
                $peserta[]  = (int) ($rows[$yr]->peserta      ?? 0);
                $komersil[] = (int) ($rows[$yr]->komersil     ?? 0);
                $nonKom[]   = (int) ($rows[$yr]->non_komersil ?? 0);
            }

            $title    = "Trend Tahunan ($yearStart – $yearEnd)";
            if ($subSektor) $title .= " — $subSektor";
            $subtitle = 'Event & Peserta per tahun';

        // ── MONTHLY view (single year) ───────────────────────────────────
        } else {
            $rows = Event::valid()
                ->byYear($yearEnd)
                ->bySubSektor($subSektor)
                ->where('is_primary_row', true)
                ->selectRaw('EXTRACT(MONTH FROM tanggal)::integer as mo,
                    count(*) as cnt, sum(jumlah_peserta) as peserta,
                    COUNT(CASE WHEN komersial = \'Komersial\' THEN 1 END) as komersil,
                    COUNT(CASE WHEN komersial = \'Non Komersial\' THEN 1 END) as non_komersil')
                ->groupBy(DB::raw('EXTRACT(MONTH FROM tanggal)'))
                ->get()->keyBy('mo');

            $monthNames = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];
            $labels = $events = $peserta = $komersil = $nonKom = [];
            for ($i = 1; $i <= 12; $i++) {
                $labels[]   = $monthNames[$i - 1];
                $events[]   = (int) ($rows[$i]->cnt          ?? 0);
                $peserta[]  = (int) ($rows[$i]->peserta      ?? 0);
                $komersil[] = (int) ($rows[$i]->komersil     ?? 0);
                $nonKom[]   = (int) ($rows[$i]->non_komersil ?? 0);
            }

            $title    = "Trend Aktivitas Bulanan $yearEnd";
            if ($subSektor) $title .= " — $subSektor";
            $subtitle = 'Event & Peserta per bulan — ' . $yearEnd;
        }

        return compact('labels', 'events', 'peserta', 'komersil', 'nonKom', 'title', 'subtitle');
    }
}
