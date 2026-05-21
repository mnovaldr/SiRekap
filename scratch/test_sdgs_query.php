<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use Illuminate\Support\Facades\DB;

$totalEvents = Event::count();
$nonNullCount = Event::whereNotNull('program_sdgs')->count();
$emptyStringCount = Event::where('program_sdgs', '')->count();

echo "Total: $totalEvents\n";
echo "Non-null SDGs: $nonNullCount\n";
echo "Empty string SDGs: $emptyStringCount\n";

if ($nonNullCount > 0) {
    $samples = Event::whereNotNull('program_sdgs')->where('program_sdgs', '!=', '')->limit(10)->pluck('program_sdgs');
    echo "Samples:\n";
    foreach ($samples as $s) {
        echo " - '$s'\n";
    }
}
