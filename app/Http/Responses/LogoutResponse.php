<?php

namespace App\Http\Responses;

use Filament\Http\Responses\Auth\Contracts\LogoutResponse as LogoutResponseContract;
use Illuminate\Http\RedirectResponse;

class LogoutResponse implements LogoutResponseContract
{
    public function toResponse($request): RedirectResponse
    {
        // Session cleanup sudah ditangani oleh Logout event listener di AppServiceProvider
        return redirect()->to('/admin/login?logged_out=1');
    }
}
