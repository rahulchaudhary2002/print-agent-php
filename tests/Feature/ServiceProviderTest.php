<?php

declare(strict_types=1);

use PrintAgent\Sdk\Contracts\PrintAgentClientContract;

it('merges the default configuration', function () {
    expect(config('print-agent.base_url'))->toBe('http://127.0.0.1:3210/api/v1');
    expect(config('print-agent.timeout'))->toBe(10);
    expect(config('print-agent.retry.times'))->toBe(2);
});

it('binds the client as a singleton under multiple aliases', function () {
    expect(app(PrintAgentClientContract::class))->toBe(app('print-agent'));
});

it('does not register optional routes by default', function () {
    expect(function () {
        route('print-agent.health');
    })->toThrow(\Symfony\Component\Routing\Exception\RouteNotFoundException::class);
});
