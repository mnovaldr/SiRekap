<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// GET-based logout route (no CSRF needed) - always works even on expired sessions
Route::get('/do-logout', function () {
    Auth::logout();
    
    try {
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    } catch (\Exception $e) {
        // session may already be invalid, ignore
    }

    return redirect('/login?logged_out=1');
})->name('admin.do-logout');

// Redirect legacy /admin path to root
Route::get('/admin', function () {
    return redirect('/');
});

Route::get('/download-template', function () {
    $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
    
    $months = [
        'Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni',
        'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'
    ];
    
    $spreadsheet->removeSheetByIndex(0);
    
    foreach ($months as $idx => $month) {
        $sheet = $spreadsheet->createSheet($idx);
        $sheet->setTitle($month);
        
        $headers = [
            'No', 
            'Nama Organisasi', 
            'Bidang', 
            'Jenis Kegiatan', 
            'Sub Sektor', 
            'Komersial', 
            'Program SDGS', 
            'Judul Kegiatan', 
            'Tanggal', 
            'Lantai', 
            'Lokasi', 
            'Jumlah Peserta', 
            'PIC', 
            'No. Telf', 
            'Email'
        ];
        
        $sheet->fromArray($headers, null, 'A1');
        
        foreach (range('A', 'O') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }
        
        $headerRange = 'A1:O1';
        $styleArray = [
            'font' => [
                'bold' => true,
                'color' => ['rgb' => 'FFFFFF'],
                'size' => 10,
                'name' => 'Inter'
            ],
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => ['rgb' => '4F46E5'],
            ],
            'alignment' => [
                'horizontal' => \PhpOffice\PhpSpreadsheet\Style\Alignment::HORIZONTAL_CENTER,
                'vertical' => \PhpOffice\PhpSpreadsheet\Style\Alignment::VERTICAL_CENTER,
            ]
        ];
        $sheet->getStyle($headerRange)->applyFromArray($styleArray);
        
        $sampleData = [
            '1',
            'Komunitas Musik Indie Malang',
            'Musik',
            'Pertunjukan',
            'Seni Pertunjukan',
            'Non Komersial',
            'SDGS 8, SDGS 12',
            'Konser Malam Musik Kreatif',
            '15 Januari 2025',
            '4',
            'Studio Musik',
            '150',
            'Budi Setiawan',
            '08123456789',
            'budi@gmail.com'
        ];
        $sheet->fromArray($sampleData, null, 'A2');
    }
    
    $spreadsheet->setActiveSheetIndex(0);
    
    $response = new \Symfony\Component\HttpFoundation\StreamedResponse(function () use ($spreadsheet) {
        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
    });
    
    $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    $response->headers->set('Content-Disposition', 'attachment;filename="Template_Import_Event_MCC.xlsx"');
    $response->headers->set('Cache-Control', 'max-age=0');
    
    return $response;
})->name('admin.download-template');

Route::get('/admin/{any}', function () {
    return redirect('/');
})->where('any', '.*');
