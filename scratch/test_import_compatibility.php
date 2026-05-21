<?php
ini_set('memory_limit', '2048M');
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ExcelImportService;

$files = [
    '2025' => 'storage/app/private/livewire-tmp/0W5e0zIdLgWjbM2jCs5FIyDUc0oggF-metaUmVrYXAgRXZlbnQgTUNDIDIwMjUgKDEpLnhsc3g=-.xlsx'
];

foreach ($files as $year => $filePath) {
    echo "========================================\n";
    echo "Testing $year file: " . basename($filePath) . "\n";
    if (!file_exists($filePath)) {
        echo "File does not exist at: $filePath\n";
        continue;
    }
    
    try {
        $service = new ExcelImportService();
        $result = $service->parseExcel($filePath);
        
        echo "Parsed " . count($result['data']) . " total rows.\n";
        echo "Found columns:\n";
        print_r($result['headers']);
        
        if (!empty($result['data'])) {
            echo "Sample row 0:\n";
            print_r($result['data'][0]);
            
            // Standardize test
            $std = $service->standardize($result['data']);
            echo "Standardized " . count($std) . " rows.\n";
            echo "Sample standardized row 0:\n";
            print_r($std[0]);
        }
    } catch (\Exception $e) {
        echo "Error testing $year: " . $e->getMessage() . "\n";
    }
}
echo "========================================\n";
