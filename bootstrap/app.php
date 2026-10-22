<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->trustProxies(at: '*');
        
        // Tambahkan security headers & optimasi response
        $middleware->append(\App\Http\Middleware\OptimizeResponse::class);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Tangani CSRF token mismatch (error 419) - redirect ke login tanpa menampilkan halaman error
        $exceptions->render(function (\Illuminate\Session\TokenMismatchException $e, $request) {
            // Invalidate session yang bermasalah dan buat token baru
            if ($request->hasSession()) {
                $request->session()->invalidate();
                $request->session()->regenerateToken();
            }

            // Livewire request → kembalikan JSON agar Livewire redirect ke login
            if ($request->hasHeader('X-Livewire')) {
                return response()->json([
                    'effects' => ['redirect' => '/login?logged_out=1'],
                ]);
            }

            // AJAX / JSON request biasa
            if ($request->expectsJson()) {
                return response()->json([
                    'message' => 'Sesi Anda telah berakhir. Silakan login kembali.',
                    'redirect' => '/login?logged_out=1',
                ], 419);
            }

            // Request biasa (form submit, navigasi) → redirect ke login
            return redirect('/login?logged_out=1');
        });
    })->create();
