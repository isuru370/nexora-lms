<?php

namespace App\Support;

use Illuminate\Support\Facades\Http;
use Throwable;

/**
 * YouTube helpers. We only ever store the 11-character video id.
 */
final class YouTube
{
    private const ID = '[A-Za-z0-9_-]{11}';

    /**
     * Accepts youtu.be/ID, youtube.com/watch?v=ID, /shorts/ID, /live/ID,
     * /embed/ID, m.youtube.com, youtube-nocookie.com, or the bare id.
     */
    public static function parseId(?string $input): ?string
    {
        $input = trim((string) $input);

        if ($input === '') {
            return null;
        }

        if (preg_match('/^'.self::ID.'$/', $input)) {
            return $input;
        }

        $patterns = [
            '~youtu\.be/('.self::ID.')~i',
            '~youtube(?:-nocookie)?\.com/(?:.*[?&]v=|embed/|shorts/|live/|v/)('.self::ID.')~i',
        ];

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $input, $m)) {
                return $m[1];
            }
        }

        return null;
    }

    public static function thumbnail(string $id): string
    {
        return "https://i.ytimg.com/vi/{$id}/hqdefault.jpg";
    }

    public static function embedUrl(string $id): string
    {
        return "https://www.youtube-nocookie.com/embed/{$id}?rel=0&playsinline=1&modestbranding=1";
    }

    /**
     * Title from YouTube oEmbed (works for public and unlisted videos).
     * Returns null when offline, private or removed.
     */
    public static function title(string $id): ?string
    {
        try {
            $response = Http::timeout(5)->acceptJson()->get('https://www.youtube.com/oembed', [
                'url' => "https://www.youtube.com/watch?v={$id}",
                'format' => 'json',
            ]);

            return $response->successful() ? (string) $response->json('title') : null;
        } catch (Throwable) {
            return null;
        }
    }
}
