<?php

namespace App\Support\Health;

final class CheckResult
{
    public const OK = 'ok';

    public const WARN = 'warn';

    public const FAIL = 'fail';

    public function __construct(
        public readonly string $label,
        public readonly string $status,
        public readonly ?string $detail = null,
    ) {}

    public function failed(): bool
    {
        return $this->status === self::FAIL;
    }
}
