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

        // Compress response jika browser support gzip (hanya untuk GET requests)
        if ($request->isMethod('GET') && $this->shouldCompress($request, $response)) {
            $content = $response->getContent();
            if ($content && strlen($content) > 1024) { // Only compress if > 1KB
                $compressed = gzencode($content, 6); // Level 6 balance speed/size
                if ($compressed !== false) {
                    $response->setContent($compressed);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->set('Content-Length', strlen($compressed));
                }
            }
        }

        return $response;
    }

    private function shouldCompress(Request $request, Response $response): bool
    {
        // Check if browser accepts gzip
        $acceptEncoding = $request->header('Accept-Encoding', '');
        if (stripos($acceptEncoding, 'gzip') === false) {
            return false;
        }

        // Check if response is already compressed
        if ($response->headers->has('Content-Encoding')) {
            return false;
        }

        // Only compress text-based content
        $contentType = $response->headers->get('Content-Type', '');
        $compressibleTypes = ['text/', 'application/json', 'application/javascript', 'application/xml'];
        
        foreach ($compressibleTypes as $type) {
            if (stripos($contentType, $type) !== false) {
                return true;
            }
        }

        return false;
    }
}
