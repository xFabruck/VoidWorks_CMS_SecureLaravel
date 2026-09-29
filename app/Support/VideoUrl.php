<?php

namespace App\Support;

class VideoUrl
{
    private const YOUTUBE_HOSTS = ['youtube.com', 'www.youtube.com', 'm.youtube.com', 'youtu.be'];

    private const VIMEO_HOSTS = ['vimeo.com', 'www.vimeo.com', 'player.vimeo.com'];

    public static function extractId(string $url, string $provider): ?string
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return null;
        }

        $parts = parse_url($url);
        if (! is_array($parts)
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && $parts['port'] !== 443)
            || isset($parts['fragment'])) {
            return null;
        }

        $host = strtolower($parts['host'] ?? '');
        $path = $parts['path'] ?? '';

        if ($provider === 'youtube' && in_array($host, self::YOUTUBE_HOSTS, true)) {
            $id = self::youtubeId($host, $path, $parts['query'] ?? '');

            return $id !== null && preg_match('/^[A-Za-z0-9_-]{11}$/', $id) === 1 ? $id : null;
        }

        if ($provider === 'vimeo' && in_array($host, self::VIMEO_HOSTS, true)) {
            parse_str($parts['query'] ?? '', $parameters);
            if (isset($parameters['h'])) {
                return null;
            }

            if (preg_match('~^/(?:video/)?([0-9]+)/?$~', $path, $matches) !== 1) {
                return null;
            }

            return $matches[1];
        }

        return null;
    }

    public static function canonicalUrl(string $provider, string $videoId): string
    {
        return $provider === 'youtube'
            ? 'https://www.youtube.com/watch?v='.$videoId
            : 'https://vimeo.com/'.$videoId;
    }

    public static function thumbnailAllowed(string $url, string $provider): bool
    {
        if (! filter_var($url, FILTER_VALIDATE_URL)) {
            return false;
        }

        $parts = parse_url($url);
        if (! is_array($parts)
            || strtolower($parts['scheme'] ?? '') !== 'https'
            || isset($parts['user'])
            || isset($parts['pass'])
            || (isset($parts['port']) && $parts['port'] !== 443)) {
            return false;
        }

        $host = strtolower($parts['host'] ?? '');

        return match ($provider) {
            'youtube' => $host === 'i.ytimg.com',
            'vimeo' => $host === 'i.vimeocdn.com' || str_ends_with($host, '.vimeocdn.com'),
            default => false,
        };
    }

    private static function youtubeId(string $host, string $path, string $query): ?string
    {
        if ($host === 'youtu.be') {
            $candidate = trim($path, '/');

            return str_contains($candidate, '/') ? null : $candidate;
        }

        if ($path === '/watch') {
            parse_str($query, $parameters);

            return is_string($parameters['v'] ?? null) ? $parameters['v'] : null;
        }

        if (preg_match('~^/(?:embed|shorts|live)/([A-Za-z0-9_-]{11})/?$~', $path, $matches) === 1) {
            return $matches[1];
        }

        return null;
    }
}
