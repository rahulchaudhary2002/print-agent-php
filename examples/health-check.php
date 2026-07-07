<?php

declare(strict_types=1);

// Example: wiring the Print Agent's health into Laravel's own health check
// (routes/web.php: Route::get('/up', ...) in Laravel 11+, or a custom monitoring endpoint).

use Illuminate\Http\JsonResponse;
use PrintAgent\Sdk\Facades\PrintAgent;

final class PrintAgentHealthController
{
    public function __invoke(): JsonResponse
    {
        if (! PrintAgent::testConnection()) {
            return response()->json(['status' => 'down', 'reason' => 'Print Agent unreachable'], 503);
        }

        $health = PrintAgent::health()->health();

        return response()->json([
            'status' => $health->status,
            'version' => $health->version,
            'uptimeSeconds' => $health->uptimeSeconds,
            'queueLength' => $health->queue->length,
            'printers' => "{$health->printers->enabled}/{$health->printers->total} enabled",
            'database' => $health->databaseHealthy ? 'ok' : 'down',
        ], $health->isHealthy() ? 200 : 503);
    }
}
