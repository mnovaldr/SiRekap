<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $path = 'test.bin';
    file_put_contents($path, random_bytes(100));
    $spreadsheet = IOFactory::load($path);
    echo "Success\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    if (file_exists($path)) unlink($path);
}
