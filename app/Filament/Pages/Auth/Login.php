<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\Login as BaseLogin;
use Filament\Notifications\Notification;
use Illuminate\Contracts\Support\Htmlable;

class Login extends BaseLogin
{
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
            ->persistent()
            ->send();

        parent::throwFailureValidationException();
    }
}
