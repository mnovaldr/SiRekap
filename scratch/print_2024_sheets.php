<?php
ini_set('memory_limit', '2048M');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = 'storage/app/private/livewire-tmp/ziUhdAAXFbfdSDGj9lIYlUkvy35cqE-metaRGFzaGJvYXJkIFJla2FwIEV2ZW50IE1DQyAyMDI0Lnhsc3g=-.xlsx';
    if (!file_exists($filePath)) {
        echo "2024 File does not exist at: $filePath\n";
        exit;
    }
    
    $reader = IOFactory::createReaderForFile($filePath);
    $sheetNames = $reader->listWorksheetNames($filePath);
    
    echo "2024 Sheet Names:\n";
    foreach ($sheetNames as $idx => $name) {
        echo "- [$idx]: '$name'\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
