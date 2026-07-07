<?php

declare(strict_types=1);

// Example: an Artisan command to inspect and manage the print queue.
// php artisan print-agent:queue status|pause|resume|clear

use Illuminate\Console\Command;
use PrintAgent\Sdk\Facades\PrintAgent;

final class PrintAgentQueueCommand extends Command
{
    protected $signature = 'print-agent:queue {action : status|pause|resume|clear}';

    protected $description = 'Inspect or control the Print Agent queue';

    public function handle(): int
    {
        return match ($this->argument('action')) {
            'status' => $this->status(),
            'pause' => $this->tap(fn () => PrintAgent::queue()->pause(), 'Queue paused.'),
            'resume' => $this->tap(fn () => PrintAgent::queue()->resume(), 'Queue resumed.'),
            'clear' => $this->clear(),
            default => $this->invalidAction(),
        };
    }

    private function invalidAction(): int
    {
        $this->error('Unknown action, expected: status|pause|resume|clear');

        return self::FAILURE;
    }

    private function status(): int
    {
        $status = PrintAgent::queue()->status();
        $this->info("Queued: {$status->length}, paused: ".($status->paused ? 'yes' : 'no'));

        return self::SUCCESS;
    }

    private function clear(): int
    {
        $cancelled = PrintAgent::queue()->clear();
        $this->info("Cancelled {$cancelled} queued jobs.");

        return self::SUCCESS;
    }

    private function tap(callable $action, string $message): int
    {
        $action();
        $this->info($message);

        return self::SUCCESS;
    }
}
