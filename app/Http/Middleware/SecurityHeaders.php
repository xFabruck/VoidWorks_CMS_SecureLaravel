<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Vite;
use Symfony\Component\HttpFoundation\Response;

class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $viteOrigins = $this->localViteOrigins();
        $viteWebSocketOrigins = array_map(
            static fn (string $origin): string => (str_starts_with($origin, 'https://') ? 'wss://' : 'ws://').substr($origin, strpos($origin, '://') + 3),
            $viteOrigins,
        );

        $scriptSources = ["'self'"];
        $styleSources = ["'self'", 'https://fonts.googleapis.com'];
        $connectSources = ["'self'"];

        if ($viteOrigins !== []) {
            array_push($scriptSources, ...$viteOrigins);
            array_push($styleSources, ...$viteOrigins);
            $styleSources[] = "'unsafe-inline'";
            array_push($connectSources, ...$viteOrigins, ...$viteWebSocketOrigins);
        }

        $directives = [
            "default-src 'self'",
            'base-uri \'self\'',
            'object-src \'none\'',
            'frame-ancestors \'self\'',
            'form-action \'self\'',
            'script-src '.implode(' ', $scriptSources),
            'style-src '.implode(' ', $styleSources),
            "font-src 'self' data: https://fonts.gstatic.com",
            "img-src 'self' https://i.ytimg.com https://i.vimeocdn.com https://*.vimeocdn.com",
            'connect-src '.implode(' ', $connectSources),
            'frame-src https://www.youtube.com https://www.youtube-nocookie.com https://player.vimeo.com',
        ];

        $response = $next($request);
        $response->headers->set('X-Content-Type-Options', 'nosniff');
        $response->headers->set('Referrer-Policy', 'strict-origin-when-cross-origin');
        $response->headers->set('Permissions-Policy', 'camera=(), microphone=(), geolocation=()');
        if (! $response->headers->has('Content-Security-Policy')) {
            $response->headers->set('Content-Security-Policy', implode('; ', $directives));
        }

        if ($request->isSecure() && app()->environment('production')) {
            $response->headers->set('Strict-Transport-Security', 'max-age=31536000; includeSubDomains');
        }

        return $response;
    }

    /** @return list<string> */
    private function localViteOrigins(): array
    {
        if (app()->environment('production') || ! Vite::isRunningHot()) {
            return [];
        }

        $hotUrl = trim((string) @file_get_contents(Vite::hotFile()));
        $parts = parse_url($hotUrl);

        if (! is_array($parts) || ! in_array($parts['scheme'] ?? null, ['http', 'https'], true)) {
            return [];
        }

        $host = strtolower(trim((string) ($parts['host'] ?? ''), '[]'));

        if (! in_array($host, ['localhost', '127.0.0.1', '::1'], true)) {
            return [];
        }

        $port = isset($parts['port']) ? ':'.$parts['port'] : '';
        $hosts = ['localhost', '127.0.0.1', '[::1]'];

        return array_map(
            static fn (string $viteHost): string => $parts['scheme'].'://'.$viteHost.$port,
            $hosts,
        );
    }
}
