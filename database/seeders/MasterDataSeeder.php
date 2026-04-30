<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\MasterKategori;
use App\Models\MasterLokasi;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // Bidang
        $bidangOptions = [
            'Komunitas', 'Bisnis', 'Akademisi', 'Pemerintah',
            'Media', 'Perorangan', 'Institusi Finansial'
        ];
        foreach ($bidangOptions as $bidang) {
            MasterKategori::create([
                'type' => 'bidang',
                'raw_value' => $bidang,
                'standard_value' => $bidang,
                'frequency' => 0,
            ]);
        }

        // Jenis Kegiatan
        $jenisKegiatanOptions = [
            'Pertunjukan', 'Meeting', 'Workshop', 'Pelatihan',
            'Talkshow/Podcast', 'Take Konten Foto & Video',
            'Produksi Musik', 'Pameran', 'Kunjungan', 'Sosialisasi',
            'Kompetisi', 'Produksi Film', 'Voice Over', 'Interview',
            'Penelitian', 'Produksi Olahan', 'Konsultasi', 'Lainnya'
        ];
        foreach ($jenisKegiatanOptions as $jenis) {
            MasterKategori::create([
                'type' => 'jenis_kegiatan',
                'raw_value' => $jenis,
                'standard_value' => $jenis,
                'frequency' => 0,
            ]);
        }

        // Lokasi
        $lokasiOptions = [
            'Studio Musik', 'Studio Foto',
            'Co-Working Space 1', 'Co-Working Space 2',
            'Food Lab', 'Multi Purpose Area',
            'Amphitheater 1', 'Amphitheater 2',
            'Multifunction Room',
            'Open Public Space Barat', 'Open Public Space Utara',
            'Ruang Meeting', 'Ruang Fashion', 'KIDZONE'
        ];
        foreach ($lokasiOptions as $lokasi) {
            MasterLokasi::create([
                'raw_value' => $lokasi,
                'standard_value' => $lokasi,
                'frequency' => 0,
            ]);
        }
    }
}
