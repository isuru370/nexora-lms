<?php

namespace Tests\Unit;

use App\Support\Phone;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class PhoneTest extends TestCase
{
    /**
     * @return array<string, array{0: ?string, 1: ?string}>
     */
    public static function numbers(): array
    {
        return [
            'local with 0' => ['0771234567', '+94771234567'],
            'without 0' => ['771234567', '+94771234567'],
            'with 94' => ['94771234567', '+94771234567'],
            'e164' => ['+94771234567', '+94771234567'],
            'spaces and dashes' => ['+94 77-123 4567', '+94771234567'],
            'too short' => ['07712345', null],
            'too long' => ['077123456789', null],
            'letters' => ['abc', null],
            'null' => [null, null],
        ];
    }

    #[DataProvider('numbers')]
    public function test_it_normalizes_sri_lankan_numbers(?string $input, ?string $expected): void
    {
        $this->assertSame($expected, Phone::normalize($input));
    }

    public function test_it_displays_numbers_locally(): void
    {
        $this->assertSame('077 123 4567', Phone::display('+94771234567'));
    }
}
