<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Health;

final readonly class QueueHealth
{
    public function __construct(
        public int $length,
        public ?int $oldestPendingAgeMs,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            length: (int) $data['length'],
            oldestPendingAgeMs: isset($data['oldestPendingAgeMs']) ? (int) $data['oldestPendingAgeMs'] : null,
        );
    }
}
