<?php
require 'vendor/autoload.php';
$app = require_once 'bootstrap/app.php';
$kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
$kernel->bootstrap();

use App\Models\Event;
use Illuminate\Support\Facades\DB;

$yearEnd = 2024;
$subSektor = null;

$rows = Event::valid()
    ->whereRaw('EXTRACT(YEAR FROM "tanggal") = ?', [(int)$yearEnd])
    ->bySubSektor($subSektor)
    ->primary()
    ->selectRaw('EXTRACT(MONTH FROM "tanggal")::integer as mo,
        count(*) as cnt, sum(jumlah_peserta) as peserta,
        COUNT(CASE WHEN "komersial" = \'Komersial\' THEN 1 END) as komersil,
        COUNT(CASE WHEN "komersial" = \'Non Komersial\' THEN 1 END) as non_komersil')
    ->groupBy(DB::raw('EXTRACT(MONTH FROM "tanggal")'))
    ->get()
    ->keyBy('mo');

echo "Rows count: " . $rows->count() . "\n";
foreach ($rows as $mo => $row) {
    echo "Month $mo: " . $row->cnt . " events, " . $row->peserta . " participants\n";
}
