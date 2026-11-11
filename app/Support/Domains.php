<?php

namespace App\Support;

final class Domains
{
    public static function base(): string
    {
        return strtolower((string) config('nexora.base_domain'));
    }

    public static function central(): string
    {
        return config('nexora.central_subdomain').'.'.self::base();
    }

    public static function tenantHost(string $slug): string
    {
        return $slug.'.'.self::base();
    }

    public static function centralUrl(string $path = '/'): string
    {
        return self::url(self::central(), $path);
    }

    public static function tenantUrl(string $slug, string $path = '/'): string
    {
        return self::url(self::tenantHost($slug), $path);
    }

    private static function url(string $host, string $path): string
    {
        $path = ltrim($path, '/');

        return config('nexora.scheme').'://'.$host.($path === '' ? '' : '/'.$path);
    }
}
