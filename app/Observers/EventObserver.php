<?php

namespace App\Observers;

use App\Models\Event;
use App\Models\ActivityLog;

class EventObserver
{
    public function created(Event $event): void
    {
        ActivityLog::log(
            ActivityLog::ACTION_CREATE,
            $event,
            "Tambah event: {$event->judul_kegiatan} ({$event->nama_organisasi})",
            ['data' => $this->eventSummary($event)]
        );
    }

    public function updated(Event $event): void
    {
        $changed = $event->getChanges();
        // Jangan log perubahan timestamps saja
        unset($changed['updated_at']);
        if (empty($changed)) return;

        // Cek jika status diubah menjadi Dihapus
        if (isset($changed['status_data']) && $changed['status_data'] === 'Dihapus') {
            ActivityLog::log(
                ActivityLog::ACTION_DELETE,
                $event,
                "Hapus event (Status): {$event->judul_kegiatan} ({$event->nama_organisasi}) - Alasan: " . ($event->alasan_dihapus ?? 'Tidak ada alasan'),
                ['data' => $this->eventSummary($event), 'alasan' => $event->alasan_dihapus]
            );
            return;
        }

        // Cek jika status dipulihkan dari Dihapus
        if (isset($changed['status_data']) && $event->getOriginal('status_data') === 'Dihapus') {
            ActivityLog::log(
                'restore',
                $event,
                "Memulihkan event (Status): {$event->judul_kegiatan} ({$event->nama_organisasi})",
                ['data' => $this->eventSummary($event)]
            );
            return;
        }

        // Cek apakah hanya status_data yang berubah (validasi)
        $isValidation = array_keys($changed) === ['status_data']
            || (count($changed) === 2 && isset($changed['status_data'], $changed['catatan_review']));

        $action = $isValidation ? ActivityLog::ACTION_VALIDATE : ActivityLog::ACTION_UPDATE;
        $desc   = $isValidation
            ? "Validasi event: {$event->judul_kegiatan} → {$event->status_data}"
            : "Edit event: {$event->judul_kegiatan} ({$event->nama_organisasi})";

        ActivityLog::log(
            $action,
            $event,
            $desc,
            [
                'before' => array_intersect_key($event->getOriginal(), $changed),
                'after'  => $changed,
            ]
        );
    }

    public function deleted(Event $event): void
    {
        // Tetap catat log jika ada penghapusan fisik dari sistem/kodingan lain
        ActivityLog::log(
            'force_delete',
            $event,
            "Hapus fisik event dari database: {$event->judul_kegiatan} ({$event->nama_organisasi})",
            ['data' => $this->eventSummary($event)]
        );
    }

    public function restored(Event $event): void
    {
        // Kosongkan karena sudah dihandle di updated()
    }

    private function eventSummary(Event $event): array
    {
        return [
            'judul'        => $event->judul_kegiatan,
            'organisasi'   => $event->nama_organisasi,
            'tanggal'      => $event->tanggal?->format('Y-m-d'),
            'lokasi'       => $event->lokasi,
            'status_data'  => $event->status_data,
        ];
    }
}
