<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Providers;

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\ServiceProvider;
use PrintAgent\Sdk\Client\PrintAgentClient;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;

final class PrintAgentServiceProvider extends ServiceProvider
{
    public function register(): void
    {
        $this->mergeConfigFrom(__DIR__.'/../../config/print-agent.php', 'print-agent');

        $this->app->singleton(PrintAgentClientContract::class, function ($app): PrintAgentClient {
            /** @var array<string, mixed> $config */
            $config = $app->make('config')->get('print-agent', []);

            return new PrintAgentClient($app->make(HttpFactory::class), $config);
        });

        $this->app->alias(PrintAgentClientContract::class, PrintAgentClient::class);
        $this->app->alias(PrintAgentClientContract::class, 'print-agent');
    }

    public function boot(): void
    {
        if ($this->app->runningInConsole()) {
            $this->publishes([
                __DIR__.'/../../config/print-agent.php' => $this->app->configPath('print-agent.php'),
            ], 'print-agent-config');
        }

        $routesPath = __DIR__.'/../../routes/print-agent.php';
        $routesEnabled = (bool) $this->app->make('config')->get('print-agent.routes.enabled', false);
        if ($routesEnabled && file_exists($routesPath)) {
            $this->loadRoutesFrom($routesPath);
        }
    }

    /** @return array<int, string> */
    public function provides(): array
    {
        return [PrintAgentClientContract::class, PrintAgentClient::class, 'print-agent'];
    }
}
