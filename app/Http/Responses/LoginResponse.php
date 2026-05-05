<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LoginResponse as LoginResponseContract;
use Illuminate\Http\RedirectResponse;
use Filament\Notifications\Notification;

class LoginResponse implements LoginResponseContract
{
    public function toResponse($request)
    {
        Notification::make()
            ->title('Selamat Datang Kembali!')
            ->body('Anda berhasil masuk ke sistem SiREKAP MCC.')
            ->success()
            ->send();

        return redirect()->to(session()->pull('url.intended', filament()->getUrl()));
    }
}
