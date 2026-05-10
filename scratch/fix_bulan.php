<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use Illuminate\Support\Facades\DB;

$events = Event::whereNull('bulan')->get();
echo "Found " . $events->count() . " events with NULL bulan.\n";

$updated = 0;
foreach ($events as $event) {
    $bulan = Event::getBulanIndonesia($event->tanggal);
    if ($bulan) {
        $event->bulan = $bulan;
        $event->saveQuietly();
        $updated++;
    }
}

echo "Updated $updated events.\n";
