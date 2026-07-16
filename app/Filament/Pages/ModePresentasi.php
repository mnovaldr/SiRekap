<?php

namespace App\Filament\Pages;

use Filament\Pages\Page;
use Filament\Notifications\Notification;
use App\Services\MccEventSyncService;
use App\Models\VisitToken;
use Illuminate\Support\Str;

class ModePresentasi extends Page
{
    protected static ?string $navigationIcon = 'heroicon-o-presentation-chart-bar';
    protected static string $view = 'filament.pages.mode-presentasi';
    protected static ?string $navigationLabel = 'Mode Presentasi';
    protected static ?string $navigationGroup = 'Import / Export';
    protected static ?int $navigationSort = 3;
    protected static ?string $title = 'Kelola Layar Presentasi & Kunjungan';

    // Form Sync MCC
    public string $username = '';
    public string $password = '';
    public bool $isSyncing = false;

    // Form Generate Token
    public string $label = '';
    public string $duration = '3'; // default 3 jam

    public function mount(): void
    {
        //
    }

    /**
     * Menjalankan Sinkronisasi MCC Event otomatis.
     */
    public function startSync(MccEventSyncService $syncService): void
    {
        $this->validate([
            'username' => 'required',
            'password' => 'required',
        ], [
            'username.required' => 'Username/Email MCC Event wajib diisi.',
            'password.required' => 'Password MCC Event wajib diisi.',
        ]);

        $this->isSyncing = true;

        try {
            $result = $syncService->sync($this->username, $this->password);

            Notification::make()
                ->title('Sinkronisasi Berhasil')
                ->body("Berhasil mengimpor: {$result['imported']} event baru. Duplikat dilewati: {$result['duplicates']}. Gagal: {$result['failed']}.")
                ->success()
                ->send();

            $this->username = '';
            $this->password = '';
        } catch (\Exception $e) {
            Notification::make()
                ->title('Sinkronisasi Gagal')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        } finally {
            $this->isSyncing = false;
        }
    }

    /**
     * Membuat token kunjungan baru.
     */
    public function generateToken(): void
    {
        $this->validate([
            'label' => 'required|min:3|max:100',
            'duration' => 'required|numeric|min:1',
        ], [
            'label.required' => 'Nama kunjungan/label wajib diisi.',
            'label.min' => 'Nama kunjungan minimal 3 karakter.',
            'duration.required' => 'Durasi wajib ditentukan.',
        ]);

        $tokenStr = Str::random(32);
        $expiresAt = now()->addHours((int)$this->duration);

        VisitToken::create([
            'label' => $this->label,
            'token' => $tokenStr,
            'expires_at' => $expiresAt,
            'is_active' => true,
        ]);

        Notification::make()
            ->title('Token Kunjungan Dibuat')
            ->body("Link kunjungan untuk '{$this->label}' berhasil dibuat.")
            ->success()
            ->send();

        $this->label = '';
    }

    /**
     * Menghapus token kunjungan.
     */
    public function deleteToken(int $id): void
    {
        VisitToken::destroy($id);

        Notification::make()
            ->title('Link Kunjungan Dihapus')
            ->success()
            ->send();
    }

    /**
     * Mengaktifkan/menonaktifkan token kunjungan secara manual.
     */
    public function toggleToken(int $id): void
    {
        $token = VisitToken::find($id);
        if ($token) {
            $token->update([
                'is_active' => !$token->is_active
            ]);

            Notification::make()
                ->title('Status Link Diperbarui')
                ->success()
                ->send();
        }
    }

    /**
     * Mendapatkan daftar token kunjungan aktif/belum expired.
     */
    public function getActiveTokens(): array
    {
        return VisitToken::orderBy('created_at', 'desc')->get()->all();
    }
}
