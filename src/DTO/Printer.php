<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO;

use Carbon\CarbonImmutable;
use PrintAgent\Sdk\DTO\Enums\PrinterStatus;

final readonly class Printer
{
    /** @param array<string, mixed> $connection */
    public function __construct(
        public string $id,
        public string $name,
        public string $driver,
        public string $connectionType,
        public array $connection,
        public PrinterStatus $status,
        public bool $isDefault,
        public bool $enabled,
        public CarbonImmutable $createdAt,
        public CarbonImmutable $updatedAt,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (string) $data['id'],
            name: (string) $data['name'],
            driver: (string) $data['driver'],
            connectionType: (string) $data['connectionType'],
            connection: (array) ($data['connection'] ?? []),
            status: PrinterStatus::from((string) $data['status']),
            isDefault: (bool) ($data['isDefault'] ?? false),
            enabled: (bool) ($data['enabled'] ?? true),
            createdAt: CarbonImmutable::parse((string) $data['createdAt']),
            updatedAt: CarbonImmutable::parse((string) $data['updatedAt']),
        );
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'driver' => $this->driver,
            'connectionType' => $this->connectionType,
            'connection' => $this->connection,
            'status' => $this->status->value,
            'isDefault' => $this->isDefault,
            'enabled' => $this->enabled,
            'createdAt' => $this->createdAt->toIso8601String(),
            'updatedAt' => $this->updatedAt->toIso8601String(),
        ];
    }
}
