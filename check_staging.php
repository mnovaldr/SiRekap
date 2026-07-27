<?php
// Cek data staging yang sudah masuk
$rows = \App\Models\EventStaging::latest()->take(5)->get();
foreach ($rows as $r) {
    echo "--- ID: {$r->id} ---\n";
    echo "source_id     : {$r->source_id}\n";
    echo "judul         : {$r->judul_kegiatan}\n";
    echo "organisasi    : {$r->nama_organisasi}\n";
    echo "lokasi        : {$r->lokasi}\n";
    echo "tanggal       : {$r->tanggal}\n";
    echo "status        : {$r->status_dari_mcc}\n";
    echo "raw_data      : " . json_encode($r->raw_data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "\n\n";
}
echo "Total staging: " . \App\Models\EventStaging::count() . " baris\n";
