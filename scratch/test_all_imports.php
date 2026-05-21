<?php
ini_set('memory_limit', '2048M');
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Services\ExcelImportService;

$dir = 'storage/app/private/livewire-tmp/';
$files = glob($dir . '*.xlsx');

foreach ($files as $filePath) {
    echo "========================================\n";
    echo "Testing file: " . basename($filePath) . "\n";
    
    try {
        $service = new ExcelImportService();
        $result = $service->parseExcel($filePath);
        
        echo "Parsed " . count($result['data']) . " total rows.\n";
        echo "Found columns: " . implode(', ', $result['headers']) . "\n";
        
        if (!empty($result['data'])) {
            $std = $service->standardize($result['data']);
            echo "Standardized " . count($std) . " rows.\n";
            
            // Check years
            $years = [];
            foreach ($std as $row) {
                $y = date('Y', strtotime($row['tanggal']));
                $years[$y] = ($years[$y] ?? 0) + 1;
            }
            echo "Standardized years found: " . json_encode($years) . "\n";
            
            // Sample multi-SDG count
            $multiSdgCount = 0;
            foreach ($std as $row) {
                if (isset($row['program_sdgs']) && str_contains($row['program_sdgs'], ';')) {
                    $multiSdgCount++;
                }
            }
            echo "Rows with multiple SDGs: $multiSdgCount\n";
        }
    } catch (\Exception $e) {
        echo "Error testing file: " . $e->getMessage() . "\n";
    }
}
echo "========================================\n";
