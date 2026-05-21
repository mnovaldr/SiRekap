<?php
ini_set('memory_limit', '2048M');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = 'storage/app/private/livewire-tmp/Ta5Fl3oyfhUH9jYgT8SWOrv3ExGwDw-metaUmVrYXAgRXZlbnQgTUNDIDIwMjUgKDEpLnhsc3g=-.xlsx';
    $reader = IOFactory::createReaderForFile($filePath);
    $reader->setReadDataOnly(true);
    $wb = $reader->load($filePath);
    $sheetNames = $wb->getSheetNames();
    echo "Sheets in 2025 file: " . json_encode($sheetNames) . "\n\n";
    
    foreach (['Januari', 'Februari', 'Januari ', 'Februari '] as $name) {
        $sheet = $wb->getSheetByName($name);
        if (!$sheet) {
            continue;
        }
        echo "=== Sheet: $name ===\n";
        for ($row = 1; $row <= 6; $row++) {
            $rowData = [];
            for ($col = 1; $col <= 16; $col++) {
                $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($col);
                $rowData[] = $sheet->getCell($colLetter . $row)->getValue();
            }
            echo "Row $row: " . json_encode($rowData) . "\n";
        }
        echo "\n";
    }
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
