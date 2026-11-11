<?php

namespace App\Console\Commands;

use App\Support\Health\CheckResult;
use App\Support\Health\ReadinessChecker;
use Illuminate\Console\Command;

class DoctorCommand extends Command
{
    protected $signature = 'nexora:doctor
        {--production : Use production rules even outside production}';

    protected $description = 'Check whether NexoraLMS is ready for production';

    public function handle(ReadinessChecker $checker): int
    {
        $production = app()->isProduction() || (bool) $this->option('production');

        $this->newLine();
        $this->line('  <options=bold>NexoraLMS – production check</>'.($production ? '' : '  <fg=gray>(local rules: production-only items are warnings)</>'));
        $this->newLine();

        $results = $checker->run($production);

        foreach ($results as $result) {
            [$icon, $color] = match ($result->status) {
                CheckResult::OK => ['✓', 'green'],
                CheckResult::WARN => ['!', 'yellow'],
                default => ['✗', 'red'],
            };

            $this->line("  <fg={$color};options=bold>{$icon}</> {$result->label}");

            if ($result->detail !== null) {
                $this->line("      <fg=gray>{$result->detail}</>");
            }
        }

        $failed = count(array_filter($results, fn (CheckResult $r) => $r->failed()));
        $warnings = count(array_filter($results, fn (CheckResult $r) => $r->status === CheckResult::WARN));

        $this->newLine();

        if ($failed > 0) {
            $this->line("  <bg=red;fg=white;options=bold> NOT READY </> {$failed} problem(s) to fix".($warnings ? ", {$warnings} warning(s)" : ''));
            $this->newLine();

            return self::FAILURE;
        }

        $label = $production ? 'Production ready ✔' : 'Ready (local) ✔';
        $this->line("  <bg=green;fg=black;options=bold> {$label} </>".($warnings ? " {$warnings} warning(s)" : ''));
        $this->newLine();

        return self::SUCCESS;
    }
}
