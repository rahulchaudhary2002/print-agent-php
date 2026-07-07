<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Route;
use PrintAgent\Sdk\Facades\PrintAgent;

/*
|--------------------------------------------------------------------------
| Optional Print Agent Routes
|--------------------------------------------------------------------------
|
| Disabled unless `print-agent.routes.enabled` is true. Every route here is
| a thin proxy to a Resource call already covered by tests — no logic of
| its own beyond translating a DTO to JSON for your own dashboard/monitoring.
|
*/

$prefix = (string) config('print-agent.routes.prefix', 'print-agent');
/** @var array<int, string> $middleware */
$middleware = (array) config('print-agent.routes.middleware', ['web']);

Route::prefix($prefix)->middleware($middleware)->name('print-agent.')->group(function (): void {
    Route::get('/health', fn () => response()->json(PrintAgent::health()->health()))->name('health');
    Route::get('/status', fn () => response()->json(['connected' => PrintAgent::testConnection()]))->name('status');
    Route::get('/printers', fn () => response()->json(PrintAgent::printers()->list()))->name('printers');
    Route::get('/queue', fn () => response()->json(PrintAgent::queue()->status()))->name('queue');
});
