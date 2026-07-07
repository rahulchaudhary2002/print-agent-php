<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PrintAgent\Sdk\Builders\ReceiptBuilder;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\PrintJob;
use PrintAgent\Sdk\Exceptions\ValidationException;

function fakeJob(array $overrides = []): array
{
    return array_merge([
        'id' => 'job-1',
        'printerId' => 'printer-1',
        'applicationId' => null,
        'type' => 'document',
        'payload' => '{"sections":[]}',
        'status' => 'queued',
        'priority' => 0,
        'retryCount' => 0,
        'error' => null,
        'createdAt' => '2026-01-01T00:00:00.000Z',
        'startedAt' => null,
        'finishedAt' => null,
    ], $overrides);
}

it('submits a document built by ReceiptBuilder as a job payload string', function () {
    Http::fake([
        '*/jobs' => Http::response(['success' => true, 'message' => 'Job created', 'data' => fakeJob()], 201),
    ]);

    $document = ReceiptBuilder::make()->store('Acme')->total(10.0)->cut()->toJson();

    $job = app(PrintAgentClientContract::class)->jobs()->print(
        type: 'document',
        payload: $document,
        printerId: 'printer-1',
    );

    expect($job)->toBeInstanceOf(PrintJob::class);

    Http::assertSent(function ($request) use ($document) {
        return $request['type'] === 'document'
            && $request['payload'] === $document
            && is_string($request['payload']);
    });
});

it('rejects an empty payload before making a request', function () {
    app(PrintAgentClientContract::class)->jobs()->print('document', '');
})->throws(ValidationException::class);

it('lists jobs with pagination metadata', function () {
    Http::fake([
        '*/jobs*' => Http::response(['success' => true, 'message' => 'Success', 'data' => [
            'items' => [fakeJob(), fakeJob(['id' => 'job-2'])],
            'pagination' => ['page' => 1, 'pageSize' => 50, 'total' => 2, 'totalPages' => 1],
        ]], 200),
    ]);

    $result = app(PrintAgentClientContract::class)->jobs()->list();

    expect($result->items)->toHaveCount(2);
    expect($result->total)->toBe(2);
    expect($result->items[0])->toBeInstanceOf(PrintJob::class);
});

it('cancels and retries a job', function () {
    Http::fake([
        '*/jobs/job-1/cancel' => Http::response(['success' => true, 'message' => 'Job cancelled', 'data' => fakeJob(['status' => 'cancelled'])], 200),
        '*/jobs/job-1/retry' => Http::response(['success' => true, 'message' => 'Job re-queued', 'data' => fakeJob(['status' => 'pending'])], 200),
    ]);

    $client = app(PrintAgentClientContract::class);
    expect($client->jobs()->cancel('job-1')->status->value)->toBe('cancelled');
    expect($client->jobs()->retry('job-1')->status->value)->toBe('pending');
});
