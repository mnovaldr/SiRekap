<?php

namespace App\Filament\Pages;

use Livewire\WithFileUploads;
use Livewire\WithPagination;
use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Services\ExcelImportService;
use App\Models\Event;
use Illuminate\Support\Facades\DB;

class ImportData extends Page
{
    use WithFileUploads;
    use WithPagination;

    protected static ?string $navigationIcon = 'heroicon-o-arrow-up-tray';
    protected static string $view = 'filament.pages.import-data';
    protected static ?string $navigationLabel = 'Import Data';
    protected static ?string $navigationGroup = 'Import / Export';
    protected static ?int $navigationSort = 1;
    protected static ?string $title = 'Import Data Excel';

    public $file = null;
    public int $currentStep = 1;
    
    // Stats
    public int $totalRows = 0;
    public int $validRows = 0;
    public int $duplikatRows = 0;
    public int $reviewRows = 0;
    
    public bool $isProcessing = false;
    public string $processMessage = '';
    public bool $imported = false;

    // Pagination for preview
    public int $currentPage = 1;
    public int $perPage = 10;

    public function mount(): void
    {
        $this->currentStep = 1;
        $this->resetStats();
    }

    protected function resetStats(): void
    {
        $this->totalRows = 0;
        $this->validRows = 0;
        $this->duplikatRows = 0;
        $this->reviewRows = 0;
        $this->imported = false;
        $this->currentPage = 1;
    }

    public function updatedFile(): void
    {
        if (!$this->file) return;
        $this->processFile();
    }

    public function processFile(): void
    {
        if (!$this->file) return;

        $this->isProcessing = true;
        $this->processMessage = 'Sedang memproses file...';
        
        try {
            $path = $this->file->getRealPath();
            $service = new ExcelImportService();

            $raw = $service->parseExcel($path);
            if (empty($raw)) {
                Notification::make()->title('File kosong atau tidak valid')->danger()->send();
                $this->resetImport();
                return;
            }

            $standardized = $service->standardize($raw);
            $withStatus = $service->detectDuplicates($standardized);

            $this->totalRows = count($withStatus);
            $this->validRows = count(array_filter($withStatus, fn($r) => $r['status_data'] === 'Valid'));
            $this->duplikatRows = count(array_filter($withStatus, fn($r) => $r['status_data'] === 'Duplikat'));
            $this->reviewRows = count(array_filter($withStatus, fn($r) => $r['status_data'] === 'Review'));

            session(['import_data' => $withStatus]);
            $this->currentStep = 3;
            $this->currentPage = 1;
            
            Notification::make()->title('File siap ditinjau!')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
            $this->resetImport();
        }

        $this->isProcessing = false;
        $this->processMessage = '';
    }

    public function getPreviewDataProperty()
    {
        $data = session('import_data', []);
        $offset = ($this->currentPage - 1) * $this->perPage;
        return array_slice($data, $offset, $this->perPage);
    }

    public function nextPage(): void
    {
        if ($this->currentPage * $this->perPage < $this->totalRows) {
            $this->currentPage++;
        }
    }

    public function prevPage(): void
    {
        if ($this->currentPage > 1) {
            $this->currentPage--;
        }
    }

    public function confirmImport(): void
    {
        $data = session('import_data', []);
        if (empty($data)) {
            Notification::make()->title('Tidak ada data')->warning()->send();
            return;
        }

        $this->isProcessing = true;
        $this->processMessage = 'Menyimpan data...';

        try {
            $service = new ExcelImportService();
            $toInsert = $data; // Sertakan semua data, termasuk duplikat untuk direview nanti

            DB::transaction(function() use ($toInsert, $service) {
                foreach (array_chunk(array_values($toInsert), 100) as $chunk) {
                    foreach ($chunk as $row) {
                        $row['bulan'] = $service->getBulanIndonesia($row['tanggal']);
                        Event::create($row);
                    }
                }
            });

            session()->forget('import_data');
            $this->imported = true;
            $this->currentStep = 4;

            // Log import activity
            \App\Models\ActivityLog::log(
                \App\Models\ActivityLog::ACTION_IMPORT,
                null,
                'Import Excel: ' . count($toInsert) . ' data berhasil disimpan',
                [
                    'total'    => $this->totalRows,
                    'imported' => count($toInsert),
                    'skipped'  => $this->duplikatRows,
                    'valid'    => $this->validRows,
                    'review'   => $this->reviewRows,
                ]
            );

            Notification::make()->title('Berhasil mengimpor ' . count($toInsert) . ' data!')->success()->send();
        } catch (\Exception $e) {
            Notification::make()->title('Error saat menyimpan: ' . $e->getMessage())->danger()->send();
        }

        $this->isProcessing = false;
    }

    public function goToStep(int $step): void
    {
        if ($step < $this->currentStep) {
            $this->currentStep = $step;
        }
    }

    public function resetImport(): void
    {
        $this->file = null;
        $this->currentStep = 1;
        $this->resetStats();
        session()->forget('import_data');
    }
}
