<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PrintAgent\Sdk\Client\PrintAgentClient;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\Facades\PrintAgent;
use PrintAgent\Sdk\Resources\HealthResource;
use PrintAgent\Sdk\Resources\JobResource;
use PrintAgent\Sdk\Resources\PrinterResource;
use PrintAgent\Sdk\Resources\QueueResource;

it('resolves the same singleton as the container binding', function () {
    expect(app(PrintAgentClientContract::class))->toBe(app(PrintAgentClientContract::class));
    expect(PrintAgent::getFacadeRoot())->toBeInstanceOf(PrintAgentClient::class);
});

it('exposes every resource accessor through the facade', function () {
    expect(PrintAgent::printers())->toBeInstanceOf(PrinterResource::class);
    expect(PrintAgent::jobs())->toBeInstanceOf(JobResource::class);
    expect(PrintAgent::queue())->toBeInstanceOf(QueueResource::class);
    expect(PrintAgent::health())->toBeInstanceOf(HealthResource::class);
});

it('is usable via the print_agent() helper function identically to the facade', function () {
    Http::fake(['*/health' => Http::response(['success' => true, 'message' => 'ok', 'data' => ['status' => 'healthy']], 200)]);

    expect(print_agent()->testConnection())->toBeTrue();
    expect(print_agent())->toBeInstanceOf(PrintAgentClientContract::class);
});
