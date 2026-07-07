<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\Health;
use PrintAgent\Sdk\DTO\Metrics;

it('parses the full health snapshot', function () {
    Http::fake([
        '*/health' => Http::response(['success' => true, 'message' => 'Agent is healthy', 'data' => [
            'status' => 'healthy',
            'version' => '1.0.0',
            'uptimeSeconds' => 100,
            'queue' => ['length' => 0, 'oldestPendingAgeMs' => null],
            'renderer' => ['registered' => ['escpos'], 'healthy' => true],
            'driver' => ['registered' => ['escpos-usb', 'network']],
            'database' => ['healthy' => true],
            'printers' => ['total' => 2, 'enabled' => 2],
            'memory' => ['usedMb' => 90, 'totalMb' => 150, 'usedPercent' => 60],
            'cpu' => ['userMs' => 500, 'systemMs' => 100],
            'disk' => ['totalMb' => 10000, 'freeMb' => 5000, 'usedPercent' => 50],
        ]], 200),
    ]);

    $health = app(PrintAgentClientContract::class)->health()->health();

    expect($health)->toBeInstanceOf(Health::class);
    expect($health->isHealthy())->toBeTrue();
    expect($health->printers->total)->toBe(2);
    expect($health->disk?->freeMb)->toBe(5000);
});

it('parses metrics', function () {
    Http::fake([
        '*/metrics' => Http::response(['success' => true, 'message' => 'Success', 'data' => [
            'totalJobs' => 10, 'completedJobs' => 8, 'failedJobs' => 1, 'cancelledJobs' => 1,
            'averageRenderTimeMs' => 5, 'averagePrintTimeMs' => 20, 'averageQueueTimeMs' => 2,
            'bytesPrinted' => 4096, 'retries' => 2, 'driverUsage' => ['escpos-usb' => 8],
        ]], 200),
    ]);

    $metrics = app(PrintAgentClientContract::class)->health()->metrics();

    expect($metrics)->toBeInstanceOf(Metrics::class);
    expect($metrics->totalJobs)->toBe(10);
    expect($metrics->driverUsage)->toBe(['escpos-usb' => 8]);
});

it('tests the connection', function () {
    Http::fake(['*/health' => Http::response(['success' => true, 'message' => 'ok', 'data' => ['status' => 'healthy']], 200)]);

    expect(app(PrintAgentClientContract::class)->testConnection())->toBeTrue();
});

it('reports a failed connection test as false rather than throwing', function () {
    Http::fake(['*/health' => Http::response(null, 500)]);

    expect(app(PrintAgentClientContract::class)->testConnection())->toBeFalse();
});
