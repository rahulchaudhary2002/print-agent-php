<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO;

use PrintAgent\Sdk\DTO\Health\CpuUsage;
use PrintAgent\Sdk\DTO\Health\DiskUsage;
use PrintAgent\Sdk\DTO\Health\MemoryUsage;
use PrintAgent\Sdk\DTO\Health\PrinterCounts;
use PrintAgent\Sdk\DTO\Health\QueueHealth;
use PrintAgent\Sdk\DTO\Health\RendererHealth;

final readonly class Health
{
    /** @param array<int, string> $registeredDrivers */
    public function __construct(
        public string $status,
        public string $version,
        public int $uptimeSeconds,
        public QueueHealth $queue,
        public RendererHealth $renderer,
        public array $registeredDrivers,
        public bool $databaseHealthy,
        public PrinterCounts $printers,
        public MemoryUsage $memory,
        public CpuUsage $cpu,
        public ?DiskUsage $disk,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        /** @var array<string, mixed> $driver */
        $driver = $data['driver'] ?? ['registered' => []];

        return new self(
            status: (string) $data['status'],
            version: (string) $data['version'],
            uptimeSeconds: (int) $data['uptimeSeconds'],
            queue: QueueHealth::fromArray((array) $data['queue']),
            renderer: RendererHealth::fromArray((array) $data['renderer']),
            registeredDrivers: array_map('strval', (array) ($driver['registered'] ?? [])),
            databaseHealthy: (bool) ($data['database']['healthy'] ?? false),
            printers: PrinterCounts::fromArray((array) $data['printers']),
            memory: MemoryUsage::fromArray((array) $data['memory']),
            cpu: CpuUsage::fromArray((array) $data['cpu']),
            disk: isset($data['disk']) && is_array($data['disk']) ? DiskUsage::fromArray($data['disk']) : null,
        );
    }

    public function isHealthy(): bool
    {
        return $this->status === 'healthy';
    }
}
