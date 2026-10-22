<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class OptimizeResponse
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Security Headers
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('X-Frame-Options', 'SAMEORIGIN');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');

        // Skip optimization untuk Livewire/AJAX requests
        if ($request->isXmlHttpRequest() || 
            $request->header('X-Livewire') || 
            $request->is('livewire/*')) {
            return $response;
        }

        // Jangan optimize untuk download/streaming
        if ($response instanceof \Symfony\Component\HttpFoundation\BinaryFileResponse ||
            $response instanceof \Symfony\Component\HttpFoundation\StreamedResponse) {
            return $response;
        }

        // Skip jika bukan response sukses
        if ($response->getStatusCode() >= 400) {
            return $response;
        }

        // Add cache headers untuk static assets
        if ($request->is('css/*') || $request->is('js/*') || $request->is('images/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        }

        // Add cache headers untuk Filament assets
        if ($request->is('filament/*')) {
            $response->headers->set('Cache-Control', 'public, max-age=86400');
        }

        // Catatan: Kompresi GZIP/Brotli ditangani langsung oleh web server (Apache / Cloudflare).
        // Jangan melakukan gzencode() di middleware Laravel karena akan mengubah
        // $response->getContent() menjadi binary sebelum event RequestHandled dieksekusi,
        // sehingga Livewire v3 gagal menginjeksi <script src="/livewire/livewire.js">
        // dan menyebabkan form login error "405 Method Not Allowed".

        return $response;
    }
}
