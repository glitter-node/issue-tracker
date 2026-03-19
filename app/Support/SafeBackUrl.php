<?php

namespace App\Support;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\URL;

class SafeBackUrl
{
    public static function for(Request $request): string
    {
        $fallback = route('landing');
        $previous = URL::previous();

        if (! is_string($previous) || $previous === '' || ! self::isInternal($request, $previous) || self::requiresAuthentication($request, $previous)) {
            return $fallback;
        }

        if (self::normalize($previous) === self::normalize($request->fullUrl())) {
            return $fallback;
        }

        return $previous;
    }

    private static function isInternal(Request $request, string $url): bool
    {
        $scheme = parse_url($url, PHP_URL_SCHEME);
        $host = parse_url($url, PHP_URL_HOST);

        if ($host === null && $scheme === null) {
            return str_starts_with($url, '/');
        }

        return $host === $request->getHost();
    }

    private static function normalize(string $url): string
    {
        return rtrim($url, '/');
    }

    private static function requiresAuthentication(Request $request, string $url): bool
    {
        $path = parse_url($url, PHP_URL_PATH);

        if (! is_string($path) || $path === '') {
            return false;
        }

        try {
            $route = Route::getRoutes()->match(Request::create($path, 'GET', server: [
                'HTTP_HOST' => $request->getHost(),
            ]));
        } catch (\Throwable) {
            return false;
        }

        return in_array('auth', $route->gatherMiddleware(), true);
    }
}
