<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Contracts;

use PrintAgent\Sdk\Exceptions\ApiException;
use PrintAgent\Sdk\Exceptions\ConnectionException;
use PrintAgent\Sdk\Exceptions\TimeoutException;

/**
 * The low-level HTTP contract every Resource is built on. Resources never touch Laravel's HTTP
 * client directly — only this contract — so the whole SDK can be mocked in tests by binding a
 * fake implementation, and so `PrintAgentClient` is the only class that knows this is HTTP at all.
 */
interface PrintAgentClientContract
{
    /**
     * @param  array<string, mixed>  $query
     * @return array<string, mixed>
     *
     * @throws ApiException|ConnectionException|TimeoutException
     */
    public function get(string $uri, array $query = []): array;

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function post(string $uri, array $body = []): array;

    /**
     * @param  array<string, mixed>  $body
     * @return array<string, mixed>
     */
    public function put(string $uri, array $body = []): array;

    /** @return array<string, mixed> */
    public function delete(string $uri): array;

    /** Pings the health endpoint; returns false instead of throwing on any failure. */
    public function testConnection(): bool;
}
