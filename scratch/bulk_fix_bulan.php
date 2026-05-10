<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use Illuminate\Support\Facades\DB;

$sql = "
UPDATE events 
SET bulan = CASE EXTRACT(MONTH FROM tanggal) 
    WHEN 1 THEN 'Januari' 
    WHEN 2 THEN 'Februari' 
    WHEN 3 THEN 'Maret' 
    WHEN 4 THEN 'April' 
    WHEN 5 THEN 'Mei' 
    WHEN 6 THEN 'Juni' 
    WHEN 7 THEN 'Juli' 
    WHEN 8 THEN 'Agustus' 
    WHEN 9 THEN 'September' 
    WHEN 10 THEN 'Oktober' 
    WHEN 11 THEN 'November' 
    WHEN 12 THEN 'Desember' 
END 
WHERE bulan IS NULL
";

$affected = DB::statement($sql);
echo "Affected rows: " . (int)$affected . "\n";
