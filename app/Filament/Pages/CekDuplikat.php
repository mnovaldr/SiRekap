<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Models\Event;

class CekDuplikat extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-document-duplicate';
    protected static string $view = 'filament.pages.cek-duplikat';
    protected static ?string $navigationLabel = 'Cek Duplikat';
    protected static ?string $navigationGroup = 'Data Management';
    protected static ?int $navigationSort = 2;
    protected static ?string $title = 'Cek & Perbaiki Duplikat';

    public bool $isLoading = false;
    public bool $isProcessing = false;
    public array $duplikatGroups = [];
    public int $totalData = 0;
    public int $totalIssues = 0;
    public int $duplikatCount = 0;
    public int $reviewCount = 0;
    public int $currentPage = 1;

    public function mount(): void
    {
        $this->analyze();
    }

    // ... properti tetap sama seperti kode sebelumnya ...

    public function analyze(): void
    {
        $this->isLoading = true;
        $this->duplikatGroups = [];

        // Gunakan cursor atau get untuk mengambil data
        $events = Event::select('id','nama_organisasi','judul_kegiatan','tanggal','lokasi','lantai','status_data','jumlah_peserta')
            ->orderBy('tanggal', 'desc')
            ->get()
            ->toArray();

        $fullMap  = [];
        $shortMap = [];

        foreach ($events as $row) {
            // Key untuk Duplikat Identik (Semua Field Utama Sama)
            $kf = strtoupper(trim($row['nama_organisasi'])) . '|' .
                strtoupper(trim($row['judul_kegiatan']))   . '|' .
                ($row['tanggal'] ?? '')                  . '|' .
                strtoupper(trim($row['lokasi']))           . '|' .
                strtoupper(trim($row['lantai']));

            // Key untuk Review (Nama & Tanggal Sama tapi Ruang Beda)
            $ks = strtoupper(trim($row['nama_organisasi'])) . '|' .
                strtoupper(trim($row['judul_kegiatan']))   . '|' .
                ($row['tanggal'] ?? '');

            $fullMap[$kf][]  = $row;
            $shortMap[$ks][] = $row;
        }

        $duplikatGroups   = [];
        $reviewGroups     = [];
        $addedIds         = [];

        // 1. Deteksi Duplikat Penuh
        foreach ($fullMap as $key => $rows) {
            if (count($rows) > 1) {
                $duplikatGroups[] = [
                    'type'   => 'DUPLIKAT – Hapus',
                    'reason' => 'Data Terduplikasi Identik',
                    'key'    => $key,
                    'items'  => $rows,
                ];
                foreach ($rows as $r) $addedIds[] = $r['id'];
            }
        }

        // 2. Deteksi Konflik Lokasi (Review)
        foreach ($shortMap as $key => $rows) {
            if (count($rows) > 1) {
                $notAlreadyFull = array_filter($rows, fn($r) => !in_array($r['id'], $addedIds));
                if (count($notAlreadyFull) > 1) {
                    $reviewGroups[] = [
                        'type'   => 'REVIEW – Konflik Lokasi',
                        'reason' => 'Organisasi & Judul Sama di Ruangan Berbeda',
                        'key'    => $key,
                        'items'  => array_values($rows),
                    ];
                }
            }
        }

        $groups = array_merge($reviewGroups, $duplikatGroups);
        $this->duplikatGroups = $groups;
        $this->duplikatCount  = collect($groups)->where('type', 'DUPLIKAT – Hapus')->sum(fn($g) => count($g['items']));
        $this->reviewCount    = collect($groups)->where('type', 'REVIEW – Konflik Lokasi')->sum(fn($g) => count($g['items']));
        $this->totalIssues    = $this->duplikatCount + $this->reviewCount;

        // Reset page if current page exceeds total groups
        $totalGroups = count($groups);
        if ($this->currentPage > $totalGroups && $totalGroups > 0) {
            $this->currentPage = $totalGroups;
        } elseif ($totalGroups === 0) {
            $this->currentPage = 1;
        }

        $this->isLoading = false;
    }

    public function setPage(int $page): void
    {
        $this->currentPage = $page;
    }
    
    public function deleteRow(string $id): void
    {
        Event::find($id)?->delete();
        $this->analyze();
        Notification::make()->title('Data dihapus')->success()->send();
    }

    public function validateRow(string $id): void
    {
        Event::where('id', $id)->update(['status_data' => 'Valid', 'catatan_review' => null]);
        $this->analyze();
        Notification::make()->title('Data divalidasi')->success()->send();
    }

    public function processGroup(int $groupIndex): void
    {
        $group = $this->duplikatGroups[$groupIndex] ?? null;
        if (!$group) return;

        $this->isProcessing = true;

        if (str_contains($group['type'], 'Hapus')) {
            // Keep first, delete rest
            $toDelete = array_slice($group['items'], 1);
            foreach ($toDelete as $row) {
                Event::find($row['id'])?->delete();
            }
            Notification::make()->title(count($toDelete) . ' duplikat dihapus')->success()->send();
        } else {
            // Validate all in group
            foreach ($group['items'] as $row) {
                Event::where('id', $row['id'])->update(['status_data' => 'Valid', 'catatan_review' => null]);
            }
            Notification::make()->title(count($group['items']) . ' data divalidasi')->success()->send();
        }

        $this->isProcessing = false;
        $this->analyze();
    }
}
