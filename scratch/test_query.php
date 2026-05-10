<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;

$countTotal = Event::count();
$countValid = Event::valid()->count();
$countPrimary = Event::valid()->primary()->count();

echo "Total: $countTotal\n";
echo "Valid: $countValid\n";
echo "Primary: $countPrimary\n";

$sample = Event::valid()->primary()->limit(1)->get();
echo "Sample: " . json_encode($sample) . "\n";
