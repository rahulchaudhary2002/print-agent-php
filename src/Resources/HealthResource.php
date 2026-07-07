<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Resources;

use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\Configuration;
use PrintAgent\Sdk\DTO\Health;
use PrintAgent\Sdk\DTO\Metrics;

/** Wraps the Print Agent's `/health`, `/metrics`, `/version`, `/logs`, and `/config` endpoints. */
final class HealthResource
{
    public function __construct(private readonly PrintAgentClientContract $client) {}

    public function health(): Health
    {
        return Health::fromArray($this->client->get('/health'));
    }

    public function metrics(): Metrics
    {
        return Metrics::fromArray($this->client->get('/metrics'));
    }

    public function version(): string
    {
        $data = $this->client->get('/version');

        return (string) ($data['version'] ?? 'unknown');
    }

    /**
     * @param  array<string, mixed>  $filters  level, module, from, to, limit
     * @return array<int, array<string, mixed>>
     */
    public function logs(array $filters = []): array
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = $this->client->get('/logs', $filters);

        return $data;
    }

    public function configuration(): Configuration
    {
        return Configuration::fromArray($this->client->get('/config'));
    }
}
