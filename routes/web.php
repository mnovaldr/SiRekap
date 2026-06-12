<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;

// GET-based logout route (no CSRF needed) - always works even on expired sessions
Route::get('/do-logout', function () {
    Auth::logout();
    
    try {
        request()->session()->invalidate();
        request()->session()->regenerateToken();
    } catch (\Exception $e) {
        // session may already be invalid, ignore
    }

    return redirect('/login?logged_out=1');
})->name('admin.do-logout');

// Redirect legacy /admin path to root
Route::get('/admin', function () {
    return redirect('/');
});

Route::get('/admin/{any}', function () {
    return redirect('/');
})->where('any', '.*');
