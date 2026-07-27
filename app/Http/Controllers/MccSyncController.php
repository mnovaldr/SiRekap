<?php

namespace App\Http\Controllers;

use App\Models\EventStaging;
use App\Models\Event;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class MccSyncController extends Controller
{
    /**
     * Tampilkan halaman staging / antrean validasi
     */
    public function index()
    {
        // Mengambil antrean data dari MCC yang belum diproses
        $stagings = EventStaging::where('is_processed', false)
                        ->orderBy('created_at', 'desc')
                        ->get();
                        
        return view('admin.mcc-sync.index', compact('stagings'));
    }

    /**
     * Eksekusi sinkronisasi data dari API MCC Jadwalku
     */
    public function syncFromApi()
    {
        try {
            // Memanggil API dengan Basic Auth menggunakan kredensial
            $response = Http::withBasicAuth('mcc', 'mccmBois!')
                            ->get('https://mcc.malangkota.go.id/jadwalku/');

            if ($response->failed()) {
                return redirect()->back()->with('error', 'Gagal terhubung ke API MCC. Status: ' . $response->status());
            }

            $data = $response->json();
            $count = 0;

            // Asumsi API mengembalikan array data kegiatan
            foreach ($data as $item) {
                // Lewati jika sudah ada di tabel events
                $exists = Event::where('judul_kegiatan', $item['judul_kegiatan'] ?? '')
                               ->where('tanggal', $item['tanggal'] ?? '')
                               ->exists();
                
                if ($exists) continue;

                // Prediksi SDGs
                $predictedSdgs = $this->predictSdgs($item['judul_kegiatan'] ?? '', $item['jenis_kegiatan'] ?? '');

                // Simpan ke staging table
                EventStaging::updateOrCreate([
                    'source_id' => $item['id'] ?? null,
                    'judul_kegiatan' => $item['judul_kegiatan'] ?? '',
                ], [
                    'raw_data' => json_encode($item),
                    'jenis_kegiatan' => $item['jenis_kegiatan'] ?? '',
                    'nama_organisasi' => $item['nama_organisasi'] ?? '',
                    'lokasi' => $item['lokasi'] ?? '',
                    'tanggal' => $item['tanggal'] ?? '',
                    'status_dari_mcc' => $item['status'] ?? 'APPROVE', // BOOKING, REJECT, APPROVE
                    'predicted_sdgs' => $predictedSdgs,
                ]);

                $count++;
            }

            return redirect()->back()->with('success', "Berhasil menarik $count data baru ke antrean validasi.");

        } catch (\Exception $e) {
            Log::error('MCC Sync Error: ' . $e->getMessage());
            return redirect()->back()->with('error', 'Terjadi kesalahan sistem saat sinkronisasi.');
        }
    }

    /**
     * Logika Otomatisasi Klasifikasi SDGs menggunakan Keyword Matching
     */
    private function predictSdgs($judul, $jenis)
    {
        $text = strtolower($judul . ' ' . $jenis);

        $sdgsKeywords = [
            'SDGS 1' => ['kemiskinan', 'bantuan sosial', 'zakat', 'santunan'],
            'SDGS 2' => ['pangan', 'gizi', 'stunting', 'pertanian', 'makanan'],
            'SDGS 3' => ['kesehatan', 'medis', 'donor darah', 'olahraga', 'senam'],
            'SDGS 4' => ['pendidikan', 'pelatihan', 'workshop', 'edukasi', 'seminar', 'kampus', 'sekolah', 'belajar'],
            'SDGS 5' => ['perempuan', 'gender', 'pemberdayaan wanita'],
            'SDGS 8' => ['ekonomi', 'umkm', 'bisnis', 'pameran usaha', 'bazar', 'pasar', 'wirausaha'],
            'SDGS 9' => ['inovasi', 'industri', 'infrastruktur', 'teknologi', 'digital', 'aplikasi'],
            'SDGS 10' => ['disabilitas', 'inklusif', 'kesetaraan'],
            'SDGS 11' => ['kota', 'pemukiman', 'tata ruang'],
            'SDGS 12' => ['daur ulang', 'sampah', 'konsumsi'],
            'SDGS 13' => ['iklim', 'lingkungan', 'penghijauan', 'pohon'],
            'SDGS 16' => ['hukum', 'keadilan', 'sosialisasi peraturan', 'damai'],
            'SDGS 17' => ['kemitraan', 'kerjasama', 'kolaborasi', 'mou'],
        ];

        $matchedSdgs = [];

        foreach ($sdgsKeywords as $sdg => $keywords) {
            foreach ($keywords as $keyword) {
                if (str_contains($text, $keyword)) {
                    $matchedSdgs[] = $sdg;
                    break;
                }
            }
        }

        return !empty($matchedSdgs) ? implode(', ', $matchedSdgs) : null;
    }

    /**
     * Submit dari Admin untuk memasukkan data Staging ke tabel Events
     */
    public function approve(Request $request, $id)
    {
        $staging = EventStaging::findOrFail($id);

        $request->validate([
            'judul_kegiatan' => 'required|string',
            'jenis_kegiatan' => 'required|string',
            'program_sdgs' => 'nullable|string',
            'sub_sektor' => 'nullable|string',
        ]);

        // Simpan ke tabel events utama
        Event::create([
            'judul_kegiatan' => $request->judul_kegiatan,
            'jenis_kegiatan' => $request->jenis_kegiatan,
            'nama_organisasi' => $staging->nama_organisasi,
            'lokasi' => $staging->lokasi,
            'tanggal' => $staging->tanggal,
            'status_data' => $staging->status_dari_mcc, // Memuat BOOKING, REJECT, APPROVE
            'program_sdgs' => $request->program_sdgs,
            'sub_sektor' => $request->sub_sektor,
        ]);

        // Tandai staging sebagai sudah diproses
        $staging->update(['is_processed' => true]);

        return redirect()->back()->with('success', 'Data berhasil divalidasi dan dimasukkan ke SiREKAP.');
    }
}
