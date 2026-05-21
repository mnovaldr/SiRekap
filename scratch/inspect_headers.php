<?php
ini_set('memory_limit', '2048M');
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use PhpOffice\PhpSpreadsheet\IOFactory;
use App\Services\ExcelImportService;

class TestService extends ExcelImportService {
    public function testDetect(string $filePath) {
        $reader = IOFactory::createReaderForFile($filePath);
        $reader->setReadDataOnly(true);
        $spreadsheet = $reader->load($filePath);
        $sheetNames = $spreadsheet->getSheetNames();
        
        $validMonthsLower = array_map('strtolower', $this->validMonths);
        $bulanSheets = array_filter($sheetNames, function($n) use ($validMonthsLower) {
            return in_array(strtolower(trim($n)), $validMonthsLower);
        });
        
        foreach ($bulanSheets as $sheetName) {
            $sheet = $spreadsheet->getSheetByName($sheetName);
            $highestRow = $sheet->getHighestRow();
            $highestCol = $sheet->getHighestColumn();
            $highestColIndex = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::columnIndexFromString($highestCol);
            
            $headerSample = [];
            for ($i = 1; $i <= min(10, $highestRow); $i++) {
                $rowData = [];
                for ($colIndex = 1; $colIndex <= $highestColIndex; $colIndex++) {
                    $colLetter = \PhpOffice\PhpSpreadsheet\Cell\Coordinate::stringFromColumnIndex($colIndex);
                    $rowData[] = $sheet->getCell($colLetter . $i)->getValue();
                }
                $headerSample[] = $rowData;
            }
            
            [$headerRow, $dataStartRow, $parentRow] = $this->detectHeaderRow($headerSample);
            echo "Sheet: '$sheetName'\n";
            echo " - Detected Header Row index: " . ($headerRow !== null ? $headerRow : 'NULL') . "\n";
            echo " - Parent Row index: " . ($parentRow !== null ? $parentRow : 'NULL') . "\n";
            echo " - Data Start Row index: " . ($dataStartRow !== null ? $dataStartRow : 'NULL') . "\n";
            
            if ($headerRow !== null) {
                $rawHeaders = $headerSample[$headerRow];
                if ($parentRow !== null && isset($headerSample[$parentRow])) {
                    $rawHeaders = $this->mergeHeaderRows($headerSample[$parentRow], $headerSample[$headerRow]);
                }
                echo " - Merged Headers: " . json_encode(array_filter(array_map('trim', $rawHeaders))) . "\n";
            }
            echo "\n";
        }
    }
}

try {
    $filePath = 'storage/app/private/livewire-tmp/ziUhdAAXFbfdSDGj9lIYlUkvy35cqE-metaRGFzaGJvYXJkIFJla2FwIEV2ZW50IE1DQyAyMDI0Lnhsc3g=-.xlsx';
    $t = new TestService();
    $t->testDetect($filePath);
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
}
