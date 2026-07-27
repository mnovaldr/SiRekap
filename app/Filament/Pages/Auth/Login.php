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

    public int $loginAttempts = 0;

    public function authenticate(): ?\Filament\Http\Responses\Auth\Contracts\LoginResponse
    {
        $this->loginAttempts++;

        try {
            $this->rateLimit(5);
        } catch (\Filament\Http\Exceptions\TooManyRequestsException $exception) {
            $this->getRateLimitedNotification($exception)?->send();

            return null;
        }

        $data = $this->form->getState();

        if (! \Filament\Facades\Filament::auth()->attempt($this->getCredentialsFromFormData($data), $data['remember'] ?? false)) {
            Notification::make()
                ->danger()
                ->title('Akses Ditolak')
                ->body($this->getCredentialsDoNotMatchMessage())
                ->send();

            $this->addError('auth_failed', '1');

            return null;
        }

        $user = \Filament\Facades\Filament::auth()->user();

        if (
            ($user instanceof \Filament\Models\Contracts\FilamentUser) &&
            (! $user->canAccessPanel(\Filament\Facades\Filament::getCurrentPanel()))
        ) {
            \Filament\Facades\Filament::auth()->logout();

            Notification::make()
                ->danger()
                ->title('Akses Ditolak')
                ->body($this->getCredentialsDoNotMatchMessage())
                ->send();

            $this->addError('auth_failed', '1');

            return null;
        }

        session()->regenerate();

        return app(\Filament\Http\Responses\Auth\Contracts\LoginResponse::class);
    }

    protected function getAuthenticateFormAction(): \Filament\Actions\Action
    {
        return \Filament\Actions\Action::make('authenticate')
            ->label('Masuk')
            ->submit('authenticate')
            ->extraAttributes([
                'wire:loading.attr' => 'disabled',
                'wire:target' => 'authenticate',
            ]);
    }
}
