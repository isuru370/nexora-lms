<?php

namespace App\Console\Commands;

use Database\Seeders\PlatformSeeder;
use Illuminate\Console\Command;
use Throwable;

/**
 * One command for every deploy (Coolify "post-deployment command"):
 *
 *   php artisan nexora:deploy
 *
 * maintenance on -> migrate -> platform data -> storage link -> caches
 * -> restart queue workers -> maintenance off -> nexora:doctor
 *
 * Exits non-zero when any step or check fails, so the deploy is marked failed.
 */
class DeployCommand extends Command
{
    protected $signature = 'nexora:deploy
        {--no-maintenance : Do not put the site in maintenance mode while deploying}';

    protected $description = 'Migrate, seed platform data, cache and run the production check';

    public function handle(): int
    {
        $production = app()->isProduction();
        $maintenance = $production && ! $this->option('no-maintenance');
        $ok = true;

        $this->newLine();
        $this->line('  <options=bold>Deploying NexoraLMS</> <fg=gray>('.app()->environment().')</>');
        $this->newLine();

        try {
            if ($maintenance) {
                $this->callSilently('down', ['--retry' => 15, '--refresh' => 15]);
                $this->components->info('Maintenance mode on');
            }

            $this->components->info('Database migrations');
            $ok = $this->call('migrate', ['--force' => true]) === self::SUCCESS;

            $ok = $ok && $this->step('Platform data', fn () => $this->callSilently('db:seed', [
                '--class' => PlatformSeeder::class,
                '--force' => true,
            ]) === self::SUCCESS);

            $ok = $ok && $this->step('Storage link', fn () => file_exists(public_path('storage'))
                || $this->callSilently('storage:link') === self::SUCCESS);

            $ok = $ok && $this->step(
                $production ? 'Config, route and view caches' : 'Clear caches (local)',
                fn () => $this->callSilently($production ? 'optimize' : 'optimize:clear') === self::SUCCESS,
            );

            $ok = $ok && $this->step('Restart queue workers', fn () => $this->callSilently('queue:restart') === self::SUCCESS);
        } catch (Throwable $e) {
            $ok = false;
            $this->components->error($e->getMessage());
        } finally {
            if ($maintenance) {
                $this->callSilently('up');
                $this->components->info('Maintenance mode off');
            }
        }

        if (! $ok) {
            $this->components->error('Deploy stopped. Fix the error above and deploy again.');

            return self::FAILURE;
        }

        return $this->call('nexora:doctor');
    }

    private function step(string $label, callable $task): bool
    {
        $passed = false;

        $this->components->task($label, function () use ($task, &$passed) {
            return $passed = (bool) $task();
        });

        return $passed;
    }
}
