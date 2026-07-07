<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Resources;

use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\PaginatedResult;
use PrintAgent\Sdk\DTO\PrintJob;
use PrintAgent\Sdk\Exceptions\ValidationException;

/** Wraps the Print Agent's `/jobs` endpoints (see src/api/routes/job.routes.ts). */
final class JobResource
{
    public function __construct(private readonly PrintAgentClientContract $client) {}

    /**
     * Submits a job. `$payload` is either a raw string (for a `type` your own driver-side
     * consumer understands) or a `PrintDocument`-shaped array/JSON string produced by one of
     * this SDK's Builders — the SDK never generates ESC/POS bytes itself, it only serializes
     * the same document JSON the agent's own DocumentBuilder would produce.
     */
    public function print(
        string $type,
        string $payload,
        ?string $printerId = null,
        ?int $priority = null,
    ): PrintJob {
        if (trim($payload) === '') {
            throw new ValidationException('Job payload must not be empty');
        }

        $body = array_filter([
            'type' => $type,
            'payload' => $payload,
            'printerId' => $printerId,
            'priority' => $priority,
        ], static fn (mixed $value): bool => $value !== null);

        return PrintJob::fromArray($this->client->post('/jobs', $body));
    }

    public function get(string $id): PrintJob
    {
        return PrintJob::fromArray($this->client->get("/jobs/{$id}"));
    }

    /**
     * @param  array<string, mixed>  $filters  status, printerId, applicationId, type, createdFrom,
     *                                          createdTo, sortBy, sortOrder, page, pageSize
     * @return PaginatedResult<PrintJob>
     */
    public function list(array $filters = []): PaginatedResult
    {
        $data = $this->client->get('/jobs', $filters);

        return PaginatedResult::fromArray($data, PrintJob::fromArray(...));
    }

    public function cancel(string $id): PrintJob
    {
        return PrintJob::fromArray($this->client->post("/jobs/{$id}/cancel"));
    }

    public function retry(string $id): PrintJob
    {
        return PrintJob::fromArray($this->client->post("/jobs/{$id}/retry"));
    }

    public function delete(string $id): void
    {
        $this->client->delete("/jobs/{$id}");
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResult<PrintJob>
     */
    public function history(array $filters = []): PaginatedResult
    {
        $data = $this->client->get('/jobs/history', $filters);

        return PaginatedResult::fromArray($data, PrintJob::fromArray(...));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResult<PrintJob>
     */
    public function pending(array $filters = []): PaginatedResult
    {
        return PaginatedResult::fromArray($this->client->get('/jobs/pending', $filters), PrintJob::fromArray(...));
    }

    /**
     * @param  array<string, mixed>  $filters
     * @return PaginatedResult<PrintJob>
     */
    public function failed(array $filters = []): PaginatedResult
    {
        return PaginatedResult::fromArray($this->client->get('/jobs/failed', $filters), PrintJob::fromArray(...));
    }

    public function clearCompleted(): int
    {
        $data = $this->client->delete('/jobs/completed');

        return (int) ($data['deleted'] ?? 0);
    }
}
