<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO;

use Carbon\CarbonImmutable;
use PrintAgent\Sdk\DTO\Enums\JobStatus;

final readonly class PrintJob
{
    public function __construct(
        public string $id,
        public ?string $printerId,
        public ?string $applicationId,
        public string $type,
        public string $payload,
        public JobStatus $status,
        public int $priority,
        public int $retryCount,
        public ?string $error,
        public CarbonImmutable $createdAt,
        public ?CarbonImmutable $startedAt,
        public ?CarbonImmutable $finishedAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            printerId: isset($data['printerId']) ? (string) $data['printerId'] : null,
            applicationId: isset($data['applicationId']) ? (string) $data['applicationId'] : null,
            type: (string) $data['type'],
            payload: (string) $data['payload'],
            status: JobStatus::from((string) $data['status']),
            priority: (int) ($data['priority'] ?? 0),
            retryCount: (int) ($data['retryCount'] ?? 0),
            error: isset($data['error']) ? (string) $data['error'] : null,
            createdAt: CarbonImmutable::parse((string) $data['createdAt']),
            startedAt: isset($data['startedAt']) ? CarbonImmutable::parse((string) $data['startedAt']) : null,
            finishedAt: isset($data['finishedAt']) ? CarbonImmutable::parse((string) $data['finishedAt']) : null,
        );
    }

    public function isTerminal(): bool
    {
        return in_array($this->status, [JobStatus::Completed, JobStatus::Failed, JobStatus::Cancelled], true);
    }
}
