<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Health;

final readonly class RendererHealth
{
    /** @param array<int, string> $registered */
    public function __construct(
        public array $registered,
        public bool $healthy,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            registered: array_map('strval', (array) ($data['registered'] ?? [])),
            healthy: (bool) $data['healthy'],
        );
    }
}
