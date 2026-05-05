<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request)
    {
        // Filament sudah menangani auth()->logout(), session()->invalidate(),
        // dan session()->regenerateToken() sebelum memanggil response ini.
        // Di sini kita HANYA perlu mengarahkan ke halaman login.
        return redirect()->to(filament()->getLoginUrl() . '?logged_out=1');
    }
}
