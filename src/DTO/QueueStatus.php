<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO;

final readonly class QueueStatus
{
    public function __construct(
        public int $length,
        public ?int $oldestPendingAgeMs,
        public bool $paused,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            length: (int) ($data['length'] ?? 0),
            oldestPendingAgeMs: isset($data['oldestPendingAgeMs']) ? (int) $data['oldestPendingAgeMs'] : null,
            paused: (bool) ($data['paused'] ?? false),
        );
    }
}
