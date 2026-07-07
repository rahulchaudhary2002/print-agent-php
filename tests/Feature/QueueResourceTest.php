<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\QueueStatus;

it('reports queue status', function () {
    Http::fake([
        '*/queue/status' => Http::response(['success' => true, 'message' => 'Success', 'data' => ['length' => 3, 'oldestPendingAgeMs' => 1200, 'paused' => false]], 200),
    ]);

    $status = app(PrintAgentClientContract::class)->queue()->status();

    expect($status)->toBeInstanceOf(QueueStatus::class);
    expect($status->length)->toBe(3);
    expect($status->paused)->toBeFalse();
});

it('pauses, resumes, and clears the queue', function () {
    Http::fake([
        '*/queue/pause' => Http::response(['success' => true, 'message' => 'Queue paused', 'data' => ['paused' => true]], 200),
        '*/queue/resume' => Http::response(['success' => true, 'message' => 'Queue resumed', 'data' => ['paused' => false]], 200),
        '*/queue/clear' => Http::response(['success' => true, 'message' => 'Queue cleared', 'data' => ['cancelled' => 5]], 200),
    ]);

    $client = app(PrintAgentClientContract::class);
    $client->queue()->pause();
    $client->queue()->resume();
    expect($client->queue()->clear())->toBe(5);

    Http::assertSentCount(3);
});
