<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Health;

final readonly class PrinterCounts
{
    public function __construct(
        public int $total,
        public int $enabled,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            total: (int) $data['total'],
            enabled: (int) $data['enabled'],
        );
    }
}
