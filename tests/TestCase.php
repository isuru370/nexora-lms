<?php

namespace Tests;

use App\Support\Domains;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $this->withoutVite();
    }

    protected function centralUrl(string $path = '/'): string
    {
        return Domains::centralUrl($path);
    }

    protected function tenantUrl(string $slug, string $path = '/'): string
    {
        return Domains::tenantUrl($slug, $path);
    }
}
