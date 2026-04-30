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

    public function mount(): void
    {
        $this->analyze();
    }

    public function analyze(): void
    {
        $this->isLoading = true;
        $this->duplikatGroups = [];

        $events = Event::select('id','nama_organisasi','judul_kegiatan','tanggal','lokasi','lantai','status_data','catatan_review','bulan')
            ->orderBy('tanggal')
            ->get()
            ->toArray();

        $this->totalData = count($events);

        $fullMap  = [];
        $shortMap = [];

        foreach ($events as $row) {
            $kf = strtoupper(trim($row['nama_organisasi'])) . '||' .
                  strtoupper(trim($row['judul_kegiatan']))  . '||' .
                  ($row['tanggal'] ?? '')                   . '||' .
                  strtoupper(trim($row['lokasi']))          . '||' .
                  strtoupper(trim($row['lantai']));

            $ks = strtoupper(trim($row['nama_organisasi'])) . '||' .
                  strtoupper(trim($row['judul_kegiatan']))  . '||' .
                  ($row['tanggal'] ?? '');

            $row['_kf'] = $kf;
            $row['_ks'] = $ks;

            $fullMap[$kf][]  = $row;
            $shortMap[$ks][] = $row;
        }

        $groups   = [];
        $addedIds = [];

        // DUPLIKAT FULL
        foreach ($fullMap as $key => $rows) {
            if (count($rows) > 1) {
                $groups[] = [
                    'type'   => 'DUPLIKAT – Hapus',
                    'reason' => 'KEY FULL identik (Nama Org, Judul, Tanggal, Ruangan, Lantai sama)',
                    'key'    => $key,
                    'items'  => $rows,
                ];
                foreach ($rows as $r) $addedIds[] = $r['id'];
            }
        }

        // REVIEW SHORT
        foreach ($shortMap as $key => $rows) {
            if (count($rows) > 1) {
                $notAlreadyFull = array_filter($rows, fn($r) => !in_array($r['id'], $addedIds));
                if (count($notAlreadyFull) > 1) {
                    $groups[] = [
                        'type'   => 'REVIEW – Multi Ruangan',
                        'reason' => 'KEY SHORT identik (Nama Org, Judul, Tanggal sama — Ruangan berbeda)',
                        'key'    => $key,
                        'items'  => array_values($rows),
                    ];
                }
            }
        }

        $this->duplikatGroups = $groups;
        $this->totalIssues    = array_sum(array_map(fn($g) => count($g['items']), $groups));
        $this->duplikatCount  = array_sum(array_map(fn($g) => str_contains($g['type'], 'Hapus') ? count($g['items']) : 0, $groups));
        $this->reviewCount    = array_sum(array_map(fn($g) => str_contains($g['type'], 'Review') ? count($g['items']) : 0, $groups));

        $this->isLoading = false;
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
