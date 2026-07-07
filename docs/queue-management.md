# Queue Management

## Checking queue status

```php
use PrintAgent\Sdk\Facades\PrintAgent;

$status = PrintAgent::queue()->status();

echo "{$status->length} jobs queued, paused: " . ($status->paused ? 'yes' : 'no') . "\n";
```

## Pausing and resuming

```php
PrintAgent::queue()->pause();  // jobs stay queued, dispatching just stops
PrintAgent::queue()->resume();
```

Useful around maintenance windows — e.g. pause before restarting a printer, resume once it's
back online.

## Clearing the queue

```php
$cancelled = PrintAgent::queue()->clear();
echo "Cancelled {$cancelled} queued jobs\n";
```

This only affects jobs still *queued* — a job already mid-print finishes normally.

## Listing what's actually in the queue right now

```php
$jobs = PrintAgent::queue()->list(); // array<PrintJob>, queued jobs only
```

## Job history and filtering

```php
$page = PrintAgent::jobs()->list([
    'status' => 'failed',
    'printerId' => $printer->id,
    'page' => 1,
    'pageSize' => 25,
]);

foreach ($page->items as $job) {
    echo "{$job->id}: {$job->status->value} ({$job->error})\n";
}

echo "Page {$page->page} of {$page->totalPages}, {$page->total} total\n";
```

`history()`, `pending()`, and `failed()` all accept the same filter array and return the same
`PaginatedResult` shape.

## Retrying and cancelling

```php
PrintAgent::jobs()->retry($failedJob->id);
PrintAgent::jobs()->cancel($queuedJob->id);
```

## Handling a full queue

If the agent's configured `queueSize` is exceeded, job submission fails with an `ApiException`
(HTTP 503). Catch it as a `QueueException` in your own code if you want to distinguish "the
queue is full" from other API failures — see [error-handling.md](error-handling.md).
