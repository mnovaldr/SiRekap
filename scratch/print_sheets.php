<?php
ini_set('memory_limit', '2048M');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = 'storage/app/private/livewire-tmp/3IDBM6lqRBWrjOooETG7vhiwdvQ4Wl-metaUmVrYXAgRXZlbnQgTUNDIDIwMjUgKDEpLnhsc3g=-.xlsx';
    if (!file_exists($filePath)) {
        echo "File does not exist: $filePath\n";
        exit;
    }
    
    $reader = IOFactory::createReaderForFile($filePath);
    $sheetNames = $reader->listWorksheetNames($filePath);
    
    echo "Sheet Names:\n";
    foreach ($sheetNames as $idx => $name) {
        echo "- [$idx]: '$name'\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
