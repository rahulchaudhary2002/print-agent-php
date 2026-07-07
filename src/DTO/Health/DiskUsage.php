<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Health;

final readonly class DiskUsage
{
    public function __construct(
        public int $totalMb,
        public int $freeMb,
        public int $usedPercent,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            totalMb: (int) $data['totalMb'],
            freeMb: (int) $data['freeMb'],
            usedPercent: (int) $data['usedPercent'],
        );
    }
}
