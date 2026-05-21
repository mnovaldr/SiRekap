<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Filament\Widgets\SdgsDistributionWidget;
use App\Models\Event;

// 1. Tambahkan event dummy dengan SDGs 4 dan SDGs 5
$dummy = Event::create([
    'nama_organisasi' => 'TEST SDG ORG',
    'bidang' => 'Pemerintah',
    'jenis_kegiatan' => 'Workshop',
    'sub_sektor' => 'Aplikasi',
    'komersial' => 'Non Komersial',
    'program_sdgs' => 'SDGS 4; SDGS 5',
    'judul_kegiatan' => 'Testing SDGs Widget',
    'tanggal' => '2025-05-21',
    'bulan' => 'Mei',
    'lantai' => '2',
    'lokasi' => 'Co-Working Space 1',
    'jumlah_peserta' => 50,
    'is_primary_row' => true,
    'status_data' => 'Valid',
]);

echo "Created dummy event with ID: " . $dummy->id . "\n";

// 2. Bersihkan cache agar data baru terbaca
\Illuminate\Support\Facades\Cache::flush();

// 3. Panggil method widget
$widget = new class extends SdgsDistributionWidget {
    // Override resolveFilters to return mock values directly since we're running outside Filament request life-cycle
    protected function resolveFilters(): array
    {
        return [2025, 2025, null, null];
    }
};

$data = $widget->getSdgsData();
echo "Total events: " . $data['totalEvents'] . "\n";
echo "Total with SDGs: " . $data['totalWithSdgs'] . "\n";
echo "Counts:\n";
$hasCounts = false;
foreach ($data['counts'] as $code => $count) {
    if ($count > 0) {
        echo " - $code: $count\n";
        $hasCounts = true;
    }
}
if (!$hasCounts) {
    echo " (No SDGs counts registered)\n";
}

// 4. Bersihkan event dummy
$dummy->delete();
echo "Deleted dummy event.\n";
