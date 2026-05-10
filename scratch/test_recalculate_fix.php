<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use Illuminate\Support\Facades\DB;

try {
    echo "Resetting...\n";
    Event::query()->update(['is_primary_row' => DB::raw('false')]);
    
    echo "Fetching primary IDs...\n";
    $primaryIds = Event::valid()
        ->select(DB::raw('MIN(id) as id'))
        ->groupBy('key_short')
        ->pluck('id');
    
    echo "Count: " . $primaryIds->count() . "\n";
    
    $count = 0;
    foreach ($primaryIds->chunk(500) as $chunk) {
        $count += Event::whereIn('id', $chunk)->update(['is_primary_row' => DB::raw('true')]);
    }
    echo "Updated: $count\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
