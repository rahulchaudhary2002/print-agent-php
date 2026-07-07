<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Health;

final readonly class MemoryUsage
{
    public function __construct(
        public int $usedMb,
        public int $totalMb,
        public int $usedPercent,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            usedMb: (int) $data['usedMb'],
            totalMb: (int) $data['totalMb'],
            usedPercent: (int) $data['usedPercent'],
        );
    }
}
