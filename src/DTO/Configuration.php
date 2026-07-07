<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO;

/** Mirrors `AppConfig` in the Print Agent (src/config/config.types.ts). */
final readonly class Configuration
{
    /** @param array<int, string> $corsOrigins */
    public function __construct(
        public int $port,
        public bool $autoStart,
        public string $version,
        public ?string $defaultPrinterId,
        public string $paperWidth,
        public bool $autoCut,
        public string $loggingLevel,
        public int $queueSize,
        public int $retryCount,
        public int $renderTimeoutMs,
        public int $printTimeoutMs,
        public bool $allowRemote,
        public bool $requireApiKey,
        public array $corsOrigins,
        public int $rateLimitMax,
        public int $rateLimitWindowMs,
        public int $discoveryIntervalMs,
    ) {}

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        return new self(
            port: (int) $data['port'],
            autoStart: (bool) $data['autoStart'],
            version: (string) $data['version'],
            defaultPrinterId: isset($data['defaultPrinterId']) ? (string) $data['defaultPrinterId'] : null,
            paperWidth: (string) $data['paperWidth'],
            autoCut: (bool) $data['autoCut'],
            loggingLevel: (string) $data['loggingLevel'],
            queueSize: (int) $data['queueSize'],
            retryCount: (int) $data['retryCount'],
            renderTimeoutMs: (int) $data['renderTimeoutMs'],
            printTimeoutMs: (int) $data['printTimeoutMs'],
            allowRemote: (bool) $data['allowRemote'],
            requireApiKey: (bool) $data['requireApiKey'],
            corsOrigins: array_map('strval', (array) ($data['corsOrigins'] ?? [])),
            rateLimitMax: (int) $data['rateLimitMax'],
            rateLimitWindowMs: (int) $data['rateLimitWindowMs'],
            discoveryIntervalMs: (int) ($data['discoveryIntervalMs'] ?? 300000),
        );
    }
}
