<?php
require 'vendor/autoload.php';
use PhpOffice\PhpSpreadsheet\IOFactory;

try {
    $path = 'test.txt';
    file_put_contents($path, 'hello world');
    $spreadsheet = IOFactory::load($path);
    echo "Success\n";
} catch (\Exception $e) {
    echo "Error: " . $e->getMessage() . "\n";
} finally {
    unlink($path);
}
