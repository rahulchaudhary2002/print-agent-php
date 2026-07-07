<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Resources;

use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\PrintJob;
use PrintAgent\Sdk\DTO\QueueStatus;

/** Wraps the Print Agent's `/queue` endpoints (see src/api/routes/queue.routes.ts). */
final class QueueResource
{
    public function __construct(private readonly PrintAgentClientContract $client) {}

    /** @return array<int, PrintJob> */
    public function list(): array
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = $this->client->get('/queue');

        return array_map(PrintJob::fromArray(...), $items);
    }

    public function status(): QueueStatus
    {
        return QueueStatus::fromArray($this->client->get('/queue/status'));
    }

    public function pause(): void
    {
        $this->client->post('/queue/pause');
    }

    public function resume(): void
    {
        $this->client->post('/queue/resume');
    }

    /** @return int number of jobs cancelled */
    public function clear(): int
    {
        $data = $this->client->post('/queue/clear');

        return (int) ($data['cancelled'] ?? 0);
    }
}
