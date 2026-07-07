<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Facades;

use Illuminate\Support\Facades\Facade;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\Resources\HealthResource;
use PrintAgent\Sdk\Resources\JobResource;
use PrintAgent\Sdk\Resources\PrinterResource;
use PrintAgent\Sdk\Resources\QueueResource;

/**
 * @method static PrinterResource printers()
 * @method static JobResource jobs()
 * @method static QueueResource queue()
 * @method static HealthResource health()
 * @method static array<string, mixed> get(string $uri, array $query = [])
 * @method static array<string, mixed> post(string $uri, array $body = [])
 * @method static array<string, mixed> put(string $uri, array $body = [])
 * @method static array<string, mixed> delete(string $uri)
 * @method static bool testConnection()
 *
 * @see \PrintAgent\Sdk\Client\PrintAgentClient
 */
final class PrintAgent extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return PrintAgentClientContract::class;
    }
}
