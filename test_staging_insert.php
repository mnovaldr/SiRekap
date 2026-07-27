<?php
// Test insert ke event_stagings
$s = new \App\Models\EventStaging();
$s->source_id = 'TEST-002';
$s->judul_kegiatan = 'Test Event';
$s->nama_organisasi = 'Organisasi Test';
$s->lokasi = 'MCC Hall';
$s->tanggal = '2026-07-01';
$s->status_dari_mcc = 'BOOKING';
// is_processed tidak diisi - pakai default DB

try {
    $s->save();
    echo "OK: ID=" . $s->id . "\n";
} catch (\Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "Trace: " . $e->getTraceAsString() . "\n";
}
