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
    public array $foundColumns = [];
    public array $detectedMonths = [];
    public ?string $importUuid = null;

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
        $this->detectedMonths = [];
    }

    public function updatedFile(): void
    {
        if (!$this->file) return;

        // Validasi ekstensi secara eksplisit untuk notifikasi yang lebih cepat
        $extension = strtolower($this->file->getClientOriginalExtension());
        if (!in_array($extension, ['xlsx', 'xls'])) {
            Notification::make()
                ->title('Format File Tidak Didukung')
                ->body('File yang Anda pilih berformat .' . $extension . '. Sistem hanya mendukung file Excel (.xlsx atau .xls).')
                ->danger()
                ->persistent() // Biar tidak cepat hilang
                ->send();
            $this->file = null;
            return;
        }

        try {
            $this->validate([
                'file' => 'max:10240',
            ], [
                'file.max' => 'Ukuran file maksimal 10MB',
            ]);
            
            $this->processFile();
        } catch (\Illuminate\Validation\ValidationException $e) {
            $this->file = null;
            Notification::make()
                ->title('Gagal Unggah')
                ->body($e->validator->errors()->first('file'))
                ->danger()
                ->send();
        } catch (\Throwable $e) {
            $this->file = null;
            Notification::make()
                ->title('Error')
                ->body($e->getMessage())
                ->danger()
                ->send();
        }
    }

    public function processFile(): void
    public function processFile(): void
    {
        if (!$this->file) return;

        // Cegah timeout saat memproses file Excel 2025 yang besar (12 sheet)
        set_time_limit(600); // 10 menit max execution (naikkan dari 5 menit)
        ini_set('memory_limit', '2048M'); // Naikkan dari 1024M ke 2048M

        $this->isProcessing = true;
        $this->processMessage = 'Sedang memproses file...';
        
        try {
            $path = $this->file->getRealPath();
            $service = new ExcelImportService();

            $result = $service->parseExcel($path);
            $raw = $result['data'] ?? [];
            $this->foundColumns = $result['headers'] ?? [];

            if (empty($raw)) {
                Notification::make()
                    ->title('Data tidak ditemukan')
                    ->body('File Excel tidak memiliki sheet dengan nama bulan (Januari-Desember) atau data kosong.')
                    ->warning()
                    ->send();
                $this->resetImport();
                return;
            }

            $standardized = $service->standardize($raw);
            $withStatus = $service->detectDuplicates($standardized);

            $detected = [];
            foreach ($withStatus as $row) {
                $sheet = $row['_sheet'] ?? $row['bulan'] ?? 'Unknown';
                $detected[$sheet] = ($detected[$sheet] ?? 0) + 1;
            }
            $this->detectedMonths = $detected;

            $this->totalRows = count($withStatus);
            $this->validRows = count(array_filter($withStatus, fn($r) => $r['status_data'] === 'Valid'));
            $this->duplikatRows = count(array_filter($withStatus, fn($r) => $r['status_data'] === 'Duplikat'));
            $this->reviewRows = count(array_filter($withStatus, fn($r) => $r['status_data'] === 'Review'));

            // Write to temporary JSON file instead of session
            $this->importUuid = (string) \Illuminate\Support\Str::uuid();
            $this->writeTempData($this->importUuid, $withStatus);

            $this->currentStep = 2; // Move to validation step
            $this->currentPage = 1;
            
            Notification::make()->title('File berhasil diproses!')->success()->send();
        } catch (\Throwable $e) {
            Notification::make()->title('Gagal: ' . $e->getMessage())->danger()->send();
            $this->resetImport();
        }

        $this->isProcessing = false;
        $this->processMessage = '';
    }

    public function getPreviewDataProperty()
    {
        if (!$this->importUuid) {
            return [];
        }
        $data = $this->readTempData($this->importUuid);
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
        if (!$this->importUuid) {
            Notification::make()->title('Tidak ada data')->warning()->send();
            return;
        }
        $data = $this->readTempData($this->importUuid);
        if (empty($data)) {
            Notification::make()->title('Tidak ada data')->warning()->send();
            return;
        }

        $this->isProcessing = true;
        $this->processMessage = 'Menyimpan data...';

        try {
            $service = new ExcelImportService();
            $toInsert = array_values($data);
            $now = now();

            // Kolom yang valid di tabel events (semua kolom di $fillable + id + timestamps)
            $allowedColumns = [
                'id', 'nama_organisasi', 'bidang', 'jenis_kegiatan', 'sub_sektor',
                'komersial', 'program_sdgs', 'judul_kegiatan', 'tanggal', 'bulan',
                'lantai', 'lokasi', 'jumlah_peserta', 'is_primary_row',
                'email', 'no_telepon', 'pic',
                'status_data', 'catatan_review', 'key_full', 'key_short',
                'created_at', 'updated_at',
            ];

            DB::transaction(function() use ($toInsert, $now, $allowedColumns, $service) {
                foreach (array_chunk($toInsert, 300) as $chunk) {
                    $bulkData = [];
                    foreach ($chunk as $row) {
                        // Pastikan ID dan Timestamps terisi karena insert() tidak otomatis mengisinya
                        $row['id']         = (string) \Illuminate\Support\Str::uuid();
                        $row['created_at'] = $now;
                        $row['updated_at'] = $now;

                        // Pastikan bulan terisi — fallback dari kolom tanggal jika kosong
                        if (empty($row['bulan']) && !empty($row['tanggal'])) {
                            $row['bulan'] = $service->getBulanIndonesia($row['tanggal']);
                        }

                        // Pastikan nilai nullable tidak berupa string kosong
                        foreach (['program_sdgs', 'sub_sektor', 'email', 'no_telepon', 'pic', 'catatan_review'] as $nullable) {
                            if (isset($row[$nullable]) && $row[$nullable] === '') {
                                $row[$nullable] = null;
                            }
                        }

                        // Hapus semua kolom yang tidak ada di tabel untuk menghindari error
                        $row = array_intersect_key($row, array_flip($allowedColumns));

                        $bulkData[] = $row;
                    }
                    Event::insert($bulkData);
                }
            });

            $this->deleteTempData($this->importUuid);
            $this->importUuid = null;
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
            \Log::error('Import Error', [
                'message' => $e->getMessage(),
                'trace'   => $e->getTraceAsString(),
            ]);
            Notification::make()
                ->title('Error saat menyimpan')
                ->body($e->getMessage())
                ->danger()
                ->send();
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
        if ($this->importUuid) {
            $this->deleteTempData($this->importUuid);
            $this->importUuid = null;
        }
        $this->file = null;
        $this->currentStep = 1;
        $this->foundColumns = [];
        $this->resetStats();
    }

    protected function getTempFilePath(string $uuid): string
    {
        $dir = storage_path('app/private/import-temp');
        if (!file_exists($dir)) {
            mkdir($dir, 0755, true);
        }
        return $dir . '/' . $uuid . '.json';
    }

    protected function writeTempData(string $uuid, array $data): void
    {
        $filePath = $this->getTempFilePath($uuid);
        file_put_contents($filePath, json_encode($data));
    }

    protected function readTempData(string $uuid): array
    {
        $filePath = $this->getTempFilePath($uuid);
        if (file_exists($filePath)) {
            return json_decode(file_get_contents($filePath), true) ?? [];
        }
        return [];
    }

    protected function deleteTempData(string $uuid): void
    {
        $filePath = $this->getTempFilePath($uuid);
        if (file_exists($filePath)) {
            @unlink($filePath);
        }
    } }
}
