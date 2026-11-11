<?php

namespace App\Support\Health;

use App\Enums\Role;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Throwable;

/**
 * "Is this install ready for production?"
 *
 * Each check returns ok / warn / fail. Checks marked production-only are
 * downgraded to warnings outside production, so the same command is useful
 * on a laptop and on the server.
 */
class ReadinessChecker
{
    /**
     * @return list<CheckResult>
     */
    public function run(bool $production): array
    {
        $results = [];

        foreach ($this->checks() as [$label, $productionOnly, $check]) {
            try {
                [$passed, $detail] = $check();
            } catch (Throwable $e) {
                [$passed, $detail] = [false, Str::limit($e->getMessage(), 160)];
            }

            $status = match (true) {
                $passed === true => CheckResult::OK,
                $passed === 'warn' => CheckResult::WARN,
                $productionOnly && ! $production => CheckResult::WARN,
                default => CheckResult::FAIL,
            };

            $results[] = new CheckResult($label, $status, $passed === true ? null : $detail);
        }

        return $results;
    }

    /**
     * [label, production only, fn(): array{0: bool|'warn', 1: ?string}]
     *
     * @return list<array{0: string, 1: bool, 2: callable(): array{0: bool|string, 1: ?string}}>
     */
    private function checks(): array
    {
        return [
            ['PHP 8.3 or newer', false, fn () => [
                version_compare(PHP_VERSION, '8.3.0', '>='),
                'Running PHP '.PHP_VERSION,
            ]],

            ['PHP extensions', false, function () {
                $driver = config('database.connections.'.config('database.default').'.driver');
                $needed = ['gd', 'intl', 'mbstring', 'openssl', 'fileinfo', 'pdo_'.$driver];
                $missing = array_values(array_filter($needed, fn ($ext) => ! extension_loaded($ext)));

                return [$missing === [], 'Missing: '.implode(', ', $missing)];
            }],

            ['APP_KEY is set', false, fn () => [
                filled(config('app.key')),
                'Run: php artisan key:generate',
            ]],

            ['APP_ENV is production', true, fn () => [
                app()->isProduction(),
                'APP_ENV='.app()->environment(),
            ]],

            ['APP_DEBUG is off', true, fn () => [
                config('app.debug') === false,
                'Set APP_DEBUG=false (debug pages leak secrets)',
            ]],

            ['HTTPS URLs', true, fn () => [
                config('nexora.scheme') === 'https' && str_starts_with((string) config('app.url'), 'https://'),
                'Set APP_SCHEME=https and an https:// APP_URL',
            ]],

            ['Real base domain', true, function () {
                $base = (string) config('nexora.base_domain');

                return [
                    $base !== '' && ! str_ends_with($base, '.localhost') && ! str_ends_with($base, '.test'),
                    'APP_BASE_DOMAIN='.$base,
                ];
            }],

            ['Session cookies are per site', false, fn () => [
                blank(config('session.domain')),
                'SESSION_DOMAIN must be empty, otherwise one teacher\'s login cookie reaches every subdomain',
            ]],

            ['Secure session cookies', true, fn () => [
                config('session.secure') === true ? true : 'warn',
                'Set SESSION_SECURE_COOKIE=true',
            ]],

            ['Database timezone is UTC', false, fn () => [
                config('app.timezone') === 'UTC',
                'config/app.php timezone must stay UTC; display uses Asia/Colombo',
            ]],

            ['Database connection', false, function () {
                DB::connection()->getPdo();
                DB::select('select 1');

                return [true, null];
            }],

            ['All migrations have run', false, function () {
                $migrator = app('migrator');

                if (! $migrator->repositoryExists()) {
                    return [false, 'Migrations table missing. Run: php artisan migrate --force'];
                }

                $files = $migrator->getMigrationFiles(array_merge([database_path('migrations')], $migrator->paths()));
                $pending = array_diff(array_keys($files), $migrator->getRepository()->getRan());

                return [$pending === [], count($pending).' pending: '.implode(', ', array_slice($pending, 0, 3))];
            }],

            ['At least one Super Admin', false, fn () => [
                User::query()->whereNull('teacher_id')->where('role', Role::SuperAdmin->value)->exists(),
                'Run: php artisan nexora:create-super-admin',
            ]],

            ['Writable folders', false, function () {
                $paths = [storage_path('app'), storage_path('framework/cache'), storage_path('framework/sessions'), storage_path('framework/views'), storage_path('logs'), base_path('bootstrap/cache')];
                $notWritable = array_values(array_filter($paths, fn ($path) => ! is_writable($path)));

                return [$notWritable === [], 'Not writable: '.implode(', ', $notWritable)];
            }],

            ['Public storage link', true, fn () => [
                file_exists(public_path('storage')),
                'Run: php artisan storage:link (teacher photos and logos need it)',
            ]],

            ['Front-end built', true, fn () => [
                file_exists(public_path('build/manifest.json')),
                'Run: npm ci && npm run build',
            ]],

            ['Cache works', false, function () {
                $key = 'nexora:doctor:'.Str::random(8);
                Cache::put($key, 'ok', 10);
                $ok = Cache::get($key) === 'ok';
                Cache::forget($key);

                return [$ok, 'Cache store '.config('cache.default').' did not return the value'];
            }],

            ['Background queue', true, fn () => [
                config('queue.default') !== 'sync',
                'QUEUE_CONNECTION=sync runs jobs inside web requests; use database or redis',
            ]],

            ['Mail delivery', true, fn () => [
                in_array(config('mail.default'), ['log', 'array'], true) ? 'warn' : true,
                'MAIL_MAILER='.config('mail.default').' (emails are not really sent)',
            ]],
        ];
    }
}
