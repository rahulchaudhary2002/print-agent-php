<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Resources;

use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\Printer;
use PrintAgent\Sdk\Exceptions\ValidationException;

/** Wraps the Print Agent's `/printers` endpoints (see src/api/routes/printer.routes.ts and discovery.routes.ts). */
final class PrinterResource
{
    public function __construct(private readonly PrintAgentClientContract $client) {}

    /**
     * @return array<int, Printer>
     */
    public function list(): array
    {
        /** @var array<int, array<string, mixed>> $items */
        $items = $this->client->get('/printers');

        return array_map(Printer::fromArray(...), $items);
    }

    public function get(string $id): Printer
    {
        return Printer::fromArray($this->client->get("/printers/{$id}"));
    }

    /**
     * Raw discovery candidates as reported by the agent's discovery scanners — deliberately not
     * a typed DTO, since the shape varies per transport (USB/network/Windows/CUPS).
     *
     * @return array<int, array<string, mixed>>
     */
    public function discover(): array
    {
        /** @var array<int, array<string, mixed>> $data */
        $data = $this->client->post('/printers/discover');

        return $data;
    }

    /**
     * Live diagnostics (status/health/metrics) — not the same as the stored `status` field on the Printer DTO.
     *
     * @return array<string, mixed>
     */
    public function status(string $id): array
    {
        return $this->client->get("/printers/{$id}/status");
    }

    /** @return array<string, mixed> */
    public function test(string $id): array
    {
        return $this->client->post("/printers/{$id}/test");
    }

    /**
     * @param  array<string, mixed>  $connection
     */
    public function create(
        string $name,
        string $driver,
        string $connectionType,
        array $connection,
        bool $isDefault = false,
        bool $enabled = true,
    ): Printer {
        if (trim($name) === '') {
            throw new ValidationException('Printer name must not be empty');
        }

        return Printer::fromArray($this->client->post('/printers', [
            'name' => $name,
            'driver' => $driver,
            'connectionType' => $connectionType,
            'connection' => $connection,
            'isDefault' => $isDefault,
            'enabled' => $enabled,
        ]));
    }

    /** @param array<string, mixed> $attributes */
    public function update(string $id, array $attributes): Printer
    {
        return Printer::fromArray($this->client->put("/printers/{$id}", $attributes));
    }

    public function delete(string $id): void
    {
        $this->client->delete("/printers/{$id}");
    }

    public function setDefault(string $id): Printer
    {
        return Printer::fromArray($this->client->put("/printers/{$id}/default"));
    }

    public function enable(string $id): Printer
    {
        return Printer::fromArray($this->client->put("/printers/{$id}/enable"));
    }

    public function disable(string $id): Printer
    {
        return Printer::fromArray($this->client->put("/printers/{$id}/disable"));
    }
}
