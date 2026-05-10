<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

$bulans = Event::distinct()->pluck('bulan')->toArray();
echo "Distinct bulans: " . json_encode($bulans) . "\n";

$countValidPrimary = Event::valid()->primary()->count();
echo "Valid & Primary count: $countValidPrimary\n";

$sample = Event::valid()->primary()->whereYear('tanggal', 2024)->first();
echo "Sample 2024: " . json_encode($sample) . "\n";
