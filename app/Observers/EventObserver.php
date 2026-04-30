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
        ActivityLog::log(
            ActivityLog::ACTION_DELETE,
            $event,
            "Hapus event: {$event->judul_kegiatan} ({$event->nama_organisasi})",
            ['data' => $this->eventSummary($event)]
        );
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
