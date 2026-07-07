<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO;

final readonly class Metrics
{
    /** @param array<string, int> $driverUsage */
    public function __construct(
        public int $totalJobs,
        public int $completedJobs,
        public int $failedJobs,
        public int $cancelledJobs,
        public int $averageRenderTimeMs,
        public int $averagePrintTimeMs,
        public int $averageQueueTimeMs,
        public int $bytesPrinted,
        public int $retries,
        public array $driverUsage,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $driverUsage */
        $driverUsage = $data['driverUsage'] ?? [];

        return new self(
            totalJobs: (int) ($data['totalJobs'] ?? 0),
            completedJobs: (int) ($data['completedJobs'] ?? 0),
            failedJobs: (int) ($data['failedJobs'] ?? 0),
            cancelledJobs: (int) ($data['cancelledJobs'] ?? 0),
            averageRenderTimeMs: (int) ($data['averageRenderTimeMs'] ?? 0),
            averagePrintTimeMs: (int) ($data['averagePrintTimeMs'] ?? 0),
            averageQueueTimeMs: (int) ($data['averageQueueTimeMs'] ?? 0),
            bytesPrinted: (int) ($data['bytesPrinted'] ?? 0),
            retries: (int) ($data['retries'] ?? 0),
            driverUsage: array_map('intval', $driverUsage),
        );
    }
}
