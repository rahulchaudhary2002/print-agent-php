<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Tests;

use Orchestra\Testbench\TestCase as Orchestra;
use PrintAgent\Sdk\Facades\PrintAgent;
use PrintAgent\Sdk\Providers\PrintAgentServiceProvider;

abstract class TestCase extends Orchestra
{
    /** @return array<int, class-string> */
    protected function getPackageProviders($app): array
    {
        return [PrintAgentServiceProvider::class];
    }

    /** @return array<string, class-string> */
    protected function getPackageAliases($app): array
    {
        return ['PrintAgent' => PrintAgent::class];
    }
}
