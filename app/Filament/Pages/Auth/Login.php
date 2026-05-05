<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
    public function mount(): void
    {
        parent::mount();

        if (request()->query('logged_out')) {
            Notification::make()
                ->title('Berhasil Keluar')
                ->body('Anda telah aman keluar dari sistem SiREKAP MCC.')
                ->success()
                ->send();
        }
    }

    public function getHeading(): string | Htmlable
    {
        return 'Selamat Datang';
    }

    public function getSubheading(): string | Htmlable | null
    {
        return 'Silakan masuk ke Dashboard SiREKAP MCC';
    }

    protected function getCredentialsDoNotMatchMessage(): string
    {
        return 'Email atau kata sandi yang Anda masukkan tidak sesuai. Silakan periksa kembali.';
    }

    protected function throwFailureValidationException(): never
    {
        Notification::make()
            ->danger()
            ->title('Akses Ditolak')
            ->body($this->getCredentialsDoNotMatchMessage())
            ->send();

        parent::throwFailureValidationException();
    }
}
