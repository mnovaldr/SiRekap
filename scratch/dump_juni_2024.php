<?php
ini_set('memory_limit', '2048M');
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $filePath = 'storage/app/private/livewire-tmp/ziUhdAAXFbfdSDGj9lIYlUkvy35cqE-metaRGFzaGJvYXJkIFJla2FwIEV2ZW50IE1DQyAyMDI0Lnhsc3g=-.xlsx';
    $reader = IOFactory::createReaderForFile($filePath);
    $reader->setReadDataOnly(true);
    $wb = $reader->load($filePath);
    
    foreach (['Januari', 'Juni'] as $sheetName) {
        $sheet = $wb->getSheetByName($sheetName);
        if (!$sheet) {
            echo "Sheet $sheetName not found!\n";
            continue;
        }
        echo "=== Sheet: $sheetName ===\n";
        for ($row = 1; $row <= 6; $row++) {
            $rowData = [];
            for ($col = 1; $col <= 15; $col++) {
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
