<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Health;

final readonly class CpuUsage
{
    public function __construct(
        public int $userMs,
        public int $systemMs,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            userMs: (int) $data['userMs'],
            systemMs: (int) $data['systemMs'],
        );
    }
}
