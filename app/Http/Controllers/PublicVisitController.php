<?php

namespace App\Http\Controllers;

use App\Models\Event;
use App\Models\VisitToken;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PublicVisitController extends Controller
{
    /**
     * Menampilkan halaman digisign untuk umum tanpa token.
     */
    public function digisign()
    {
        return view('public.presentation', [
            'label' => 'Informasi Publik MCC',
            'token' => 'digisign-public'
        ]);
    }

    /**
     * Menampilkan halaman dashboard presentasi publik dengan token.
     */
    public function show(Request $request, $slug = null)
    {
        $tokenString = $request->query('token');

        if (!$tokenString) {
            abort(403, 'Akses ditolak: Token diperlukan.');
        }

        $token = VisitToken::where('token', $tokenString)->first();

        if (!$token || !$token->isValid()) {
            return view('public.expired', [
                'message' => 'Link presentasi kunjungan telah kedaluwarsa atau dinonaktifkan.'
            ]);
        }

        return view('public.presentation', [
            'label' => $token->label,
            'token' => $tokenString
        ]);
    }

    /**
     * Mengembalikan data persentase statistik (JSON) untuk halaman presentasi.
     * Hanya mengembalikan PERSENTASE, angka riil dibuang untuk keamanan data.
     */
    public function getStats(Request $request)
    {
        $tokenString = $request->query('token');

        if (!$tokenString) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($tokenString === 'digisign-public') {
            $label = 'Informasi Publik MCC';
        } else {
            $token = VisitToken::where('token', $tokenString)->first();

            if (!$token || !$token->isValid()) {
                return response()->json(['message' => 'Token expired or invalid'], 403);
            }
            $label = $token->label;
        }

        // 1. Hitung Persentase Program SDGs (Split 1 per 1)
        $sdgsList = Event::valid()
            ->whereNotNull('program_sdgs')
            ->where('program_sdgs', '!=', '')
            ->pluck('program_sdgs');

        $sdgsCounts = [];
        $totalSdgsParsed = 0;

        foreach ($sdgsList as $sdgsString) {
            // Split berdasarkan koma, titik koma, &, kata dan/and, atau lookahead untuk sdgs
            $parts = preg_split('/[;,&]|\bdan\b|\band\b|(?=\bsdgs?\s*\d)/i', $sdgsString);
            
            // Gunakan tracker agar 1 event yang menulis "SDGS 4, SDGS 4" tidak dihitung dobel
            $countedForThisEvent = [];
            
            foreach ($parts as $part) {
                $part = trim($part);
                if ($part === '') continue;
                
                if (preg_match('/sdgs?\s*(\d{1,2})/i', $part, $m)) {
                    $num = (int)$m[1];
                    if ($num >= 1 && $num <= 17) {
                        $sdgName = "SDGS $num";
                        if (!isset($countedForThisEvent[$sdgName])) {
                            $sdgsCounts[$sdgName] = ($sdgsCounts[$sdgName] ?? 0) + 1;
                            $countedForThisEvent[$sdgName] = true;
                            $totalSdgsParsed++;
                        }
                    }
                } elseif (preg_match('/^\d{1,2}$/', $part)) {
                    $num = (int)$part;
                    if ($num >= 1 && $num <= 17) {
                        $sdgName = "SDGS $num";
                        if (!isset($countedForThisEvent[$sdgName])) {
                            $sdgsCounts[$sdgName] = ($sdgsCounts[$sdgName] ?? 0) + 1;
                            $countedForThisEvent[$sdgName] = true;
                            $totalSdgsParsed++;
                        }
                    }
                }
            }
        }

        $sdgsStats = collect($sdgsCounts)->map(function ($total, $program) use ($totalSdgsParsed) {
            return [
                'program' => $program,
                'percentage' => $totalSdgsParsed > 0 ? round(($total / $totalSdgsParsed) * 100, 1) : 0
            ];
        })->sortByDesc('percentage')->values();

        // 2. Hitung Persentase Sub Sektor Ekraf
        $ekrafData = Event::valid()
            ->whereNotNull('sub_sektor')
            ->where('sub_sektor', '!=', '')
            ->select('sub_sektor', DB::raw('count(*) as total'))
            ->groupBy('sub_sektor')
            ->get();

        $totalEkraf = $ekrafData->sum('total');
        $ekrafStats = $ekrafData->map(function ($item) use ($totalEkraf) {
            return [
                'sub_sektor' => $item->sub_sektor,
                'percentage' => $totalEkraf > 0 ? round(($item->total / $totalEkraf) * 100, 1) : 0
            ];
        })->sortByDesc('percentage')->values();

        // 3. Hitung Persentase Utilitas Lokasi/Ruangan (Top 10 Teraktif)
        $lokasiData = Event::valid()
            ->whereNotNull('lokasi')
            ->where('lokasi', '!=', '')
            ->select('lokasi', DB::raw('count(*) as total'))
            ->groupBy('lokasi')
            ->get();

        $totalLokasi = $lokasiData->sum('total');
        $lokasiStats = $lokasiData->map(function ($item) use ($totalLokasi) {
            return [
                'lokasi' => $item->lokasi,
                'percentage' => $totalLokasi > 0 ? round(($item->total / $totalLokasi) * 100, 1) : 0
            ];
        })->sortByDesc('percentage')->take(10)->values();

        // 4. Hitung Rentang Tahun Dinamis Per Kategori
        $getYearText = function($query) {
            $minDate = (clone $query)->min('tanggal');
            $maxDate = (clone $query)->max('tanggal');
            if ($minDate && $maxDate) {
                $minYear = date('Y', strtotime($minDate));
                $maxYear = date('Y', strtotime($maxDate));
                return $minYear === $maxYear ? "Tahun $minYear" : "Tahun $minYear - $maxYear";
            }
            return "";
        };

        $sdgsYearText = $getYearText(Event::valid()->whereNotNull('program_sdgs')->where('program_sdgs', '!=', ''));
        $ekrafYearText = $getYearText(Event::valid()->whereNotNull('sub_sektor')->where('sub_sektor', '!=', ''));
        $lokasiYearText = $getYearText(Event::valid()->whereNotNull('lokasi')->where('lokasi', '!=', ''));

        return response()->json([
            'label' => $label,
            'sdgs' => $sdgsStats,
            'ekraf' => $ekrafStats,
            'lokasi' => $lokasiStats,
            'sdgs_year_text' => $sdgsYearText,
            'ekraf_year_text' => $ekrafYearText,
            'lokasi_year_text' => $lokasiYearText
        ]);
    }
}
