<?php

namespace Tests\Unit;

use App\Support\YouTube;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class YouTubeTest extends TestCase
{
    /**
     * @return array<string, array{0: string, 1: ?string}>
     */
    public static function links(): array
    {
        return [
            'short' => ['https://youtu.be/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'short with time' => ['https://youtu.be/dQw4w9WgXcQ?t=42', 'dQw4w9WgXcQ'],
            'watch' => ['https://www.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'watch with list' => ['https://www.youtube.com/watch?list=PL1&v=dQw4w9WgXcQ&index=2', 'dQw4w9WgXcQ'],
            'mobile' => ['https://m.youtube.com/watch?v=dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'shorts' => ['https://youtube.com/shorts/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'live' => ['https://www.youtube.com/live/dQw4w9WgXcQ?si=abc', 'dQw4w9WgXcQ'],
            'embed' => ['https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'bare id' => ['dQw4w9WgXcQ', 'dQw4w9WgXcQ'],
            'vimeo' => ['https://vimeo.com/123456', null],
            'garbage' => ['hello world', null],
        ];
    }

    #[DataProvider('links')]
    public function test_it_finds_the_video_id(string $input, ?string $expected): void
    {
        $this->assertSame($expected, YouTube::parseId($input));
    }
}
