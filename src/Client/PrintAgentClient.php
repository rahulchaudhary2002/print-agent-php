<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Client;

use Illuminate\Http\Client\ConnectionException as HttpConnectionException;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\Exceptions\ConnectionException;
use PrintAgent\Sdk\Exceptions\TimeoutException;
use PrintAgent\Sdk\Resources\HealthResource;
use PrintAgent\Sdk\Resources\JobResource;
use PrintAgent\Sdk\Resources\PrinterResource;
use PrintAgent\Sdk\Resources\QueueResource;
use PrintAgent\Sdk\Support\ResponseParser;
use Throwable;

/**
 * The single HTTP client bound as a singleton by `PrintAgentServiceProvider`. This is the only
 * class in the SDK that knows the base URL, auth headers, retry policy, and response envelope —
 * every Resource (and the Facade) goes through here. No printer/queue/document business logic
 * lives here; it only talks HTTP to the Print Agent's already-existing REST API.
 */
final class PrintAgentClient implements PrintAgentClientContract
{
    private ?PrinterResource $printers = null;

    private ?JobResource $jobs = null;

    private ?QueueResource $queue = null;

    private ?HealthResource $health = null;

    /** @param array<string, mixed> $config */
    public function __construct(
        private readonly HttpFactory $http,
        private readonly array $config,
    ) {}

    public function printers(): PrinterResource
    {
        return $this->printers ??= new PrinterResource($this);
    }

    public function jobs(): JobResource
    {
        return $this->jobs ??= new JobResource($this);
    }

    public function queue(): QueueResource
    {
        return $this->queue ??= new QueueResource($this);
    }

    public function health(): HealthResource
    {
        return $this->health ??= new HealthResource($this);
    }

    public function get(string $uri, array $query = []): array
    {
        return ResponseParser::data($this->send('get', $uri, $query));
    }

    public function post(string $uri, array $body = []): array
    {
        return ResponseParser::data($this->send('post', $uri, $body));
    }

    public function put(string $uri, array $body = []): array
    {
        return ResponseParser::data($this->send('put', $uri, $body));
    }

    public function delete(string $uri): array
    {
        return ResponseParser::data($this->send('delete', $uri));
    }

    public function testConnection(): bool
    {
        try {
            $this->get('/health');

            return true;
        } catch (Throwable) {
            return false;
        }
    }

    /** @param array<string, mixed> $payload */
    private function send(string $method, string $uri, array $payload = []): Response
    {
        try {
            // `throw()` is deliberately never enabled on the request — a 4xx/5xx here is a
            // normal, expected outcome (the agent rejected the request) handled by
            // `ResponseParser`, not a PHP-exception-worthy transport failure.
            $response = match ($method) {
                'get' => $this->pendingRequest()->get($uri, $payload),
                'post' => $this->pendingRequest()->post($uri, $payload),
                'put' => $this->pendingRequest()->put($uri, $payload),
                'delete' => $this->pendingRequest()->delete($uri, $payload),
                default => throw new \InvalidArgumentException("Unsupported HTTP method [{$method}]"),
            };
        } catch (HttpConnectionException $exception) {
            if (str_contains($exception->getMessage(), 'timed out') || str_contains($exception->getMessage(), 'timeout')) {
                throw new TimeoutException("Timed out connecting to the Print Agent at [{$uri}]", previous: $exception);
            }

            throw new ConnectionException(
                "Could not reach the Print Agent at [{$uri}] — is it running at ".(string) ($this->config['base_url'] ?? '').'?',
                previous: $exception,
            );
        }

        return $response;
    }

    private function pendingRequest(): PendingRequest
    {
        $request = $this->http
            ->baseUrl((string) ($this->config['base_url'] ?? 'http://127.0.0.1:3210/api/v1'))
            ->timeout((int) ($this->config['timeout'] ?? 10))
            ->acceptJson()
            ->asJson()
            ->withOptions(['verify' => (bool) ($this->config['verify_ssl'] ?? true)])
            ->withHeaders($this->headers());

        $retry = $this->config['retry'] ?? [];
        $times = (int) ($retry['times'] ?? 2);
        $sleepMs = (int) ($retry['sleep'] ?? 100);

        if ($times > 0) {
            $request = $request->retry($times, $sleepMs, throw: false);
        }

        return $request;
    }

    /** @return array<string, string> */
    private function headers(): array
    {
        $headers = [
            'X-Application-Name' => (string) ($this->config['application_name'] ?? 'laravel-app'),
            'X-Application-Version' => (string) ($this->config['application_version'] ?? '1.0.0'),
        ];

        $apiToken = $this->config['api_token'] ?? null;
        if (is_string($apiToken) && $apiToken !== '') {
            // The agent's optional auth mode needs both an API key and secret (from registering
            // an Application with it) — configure `api_token` as "key:secret".
            [$apiKey, $apiSecret] = array_pad(explode(':', $apiToken, 2), 2, '');
            $headers['X-Api-Key'] = $apiKey;
            $headers['X-Api-Secret'] = $apiSecret;
        }

        return $headers;
    }
}
