<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

Route::get('/', function () {
    return redirect('/admin/login');
});

// GET-based logout route (no CSRF needed) - always works even on expired sessions
Route::get('/admin/do-logout', function () {
    Auth::logout();
    
    try {
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    } catch (\Exception $e) {
        // session may already be invalid, ignore
    }

    return redirect('/admin/login?logged_out=1');
})->name('admin.do-logout');
