<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

/**
 * Data every installation needs, including production.
 *
 * Runs on every deploy (nexora:deploy), so it must be idempotent:
 * use updateOrCreate / firstOrCreate, never plain inserts.
 *
 * Plan tiers and platform settings are added here in Phase 5.
 * Never create users or passwords here — use nexora:create-super-admin.
 */
class PlatformSeeder extends Seeder
{
    public function run(): void
    {
        //
    }
}
