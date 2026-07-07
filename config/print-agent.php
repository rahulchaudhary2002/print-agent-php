<?php

declare(strict_types=1);

return [

    /*
    |--------------------------------------------------------------------------
    | Print Agent Base URL
    |--------------------------------------------------------------------------
    |
    | The Print Agent is a local background service — this should almost always
    | stay pointed at 127.0.0.1. See docs/SERVICE_MANAGEMENT.md in the agent's
    | own repository for why it defaults to localhost-only.
    |
    */
    'base_url' => env('PRINT_AGENT_BASE_URL', 'http://127.0.0.1:3210/api/v1'),

    /*
    |--------------------------------------------------------------------------
    | Timeout (seconds)
    |--------------------------------------------------------------------------
    */
    'timeout' => (int) env('PRINT_AGENT_TIMEOUT', 10),

    /*
    |--------------------------------------------------------------------------
    | API Token
    |--------------------------------------------------------------------------
    |
    | Only needed if the agent's `requireApiKey` config is enabled. Register an
    | application with the agent (POST /applications) and set this to the
    | resulting "{apiKey}:{apiSecret}" pair.
    |
    */
    'api_token' => env('PRINT_AGENT_API_TOKEN'),

    /*
    |--------------------------------------------------------------------------
    | Retry
    |--------------------------------------------------------------------------
    |
    | Applies only to connection-level failures — a 4xx/5xx response from the
    | agent is never retried, since retrying a rejected request rarely helps.
    |
    */
    'retry' => [
        'times' => (int) env('PRINT_AGENT_RETRY_TIMES', 2),
        'sleep' => (int) env('PRINT_AGENT_RETRY_SLEEP_MS', 100),
    ],

    /*
    |--------------------------------------------------------------------------
    | Verify SSL
    |--------------------------------------------------------------------------
    |
    | The agent serves plain HTTP on localhost by default; only relevant if
    | you've placed a TLS-terminating proxy in front of it.
    |
    */
    'verify_ssl' => (bool) env('PRINT_AGENT_VERIFY_SSL', true),

    /*
    |--------------------------------------------------------------------------
    | Application Identity
    |--------------------------------------------------------------------------
    |
    | Sent as informational headers on every request — useful for the agent's
    | own logs when multiple applications share one agent.
    |
    */
    'application_name' => env('PRINT_AGENT_APPLICATION_NAME', env('APP_NAME', 'laravel-app')),

    'application_version' => env('PRINT_AGENT_APPLICATION_VERSION', '1.0.0'),

    /*
    |--------------------------------------------------------------------------
    | Optional Routes
    |--------------------------------------------------------------------------
    |
    | Off by default. If enabled, registers a small set of routes (see
    | routes/print-agent.php) under your own app that simply proxy to the
    | agent's health/status endpoints — handy for wiring the agent's status
    | into your own app's monitoring without writing a controller for it.
    | This is presentation only; it adds no business logic of its own.
    |
    */
    'routes' => [
        'enabled' => (bool) env('PRINT_AGENT_ROUTES_ENABLED', false),
        'prefix' => env('PRINT_AGENT_ROUTES_PREFIX', 'print-agent'),
        'middleware' => ['web'],
    ],

];
