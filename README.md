# Print Agent PHP SDK

The official Laravel/PHP SDK for the [Universal Print Agent](../print-agent) — a local
background service that discovers, queues, and prints to receipt/label printers via a REST API
at `http://127.0.0.1:3210/api/v1`.

This package is **only an API client and document builder**. It does not duplicate the agent's
business logic, generate ESC/POS bytes, or implement any printer driver — every byte that
actually reaches a printer is produced by the agent itself.

[![PHP](https://img.shields.io/badge/PHP-8.2%2B-777BB4)]()
[![Laravel](https://img.shields.io/badge/Laravel-11%20%7C%2012-FF2D20)]()

## Requirements

- PHP 8.2+
- Laravel 11 or 12
- A running Universal Print Agent instance

## Installation

Not on Packagist (the repo is private) — install directly from git. Add to the consuming app's
`composer.json`:

```json
{
    "repositories": [
        {"type": "vcs", "url": "git@github.com:rahulchaudhary2002/print-agent-php.git"}
    ]
}
```

```bash
composer require print-agent/print-agent-php:^1.0
```

See [Installation Guide](docs/installation.md) for details. Auto-discovered once installed —
no manual provider/facade registration needed.

## Quick example

```php
use PrintAgent\Sdk\Builders\ReceiptBuilder;
use PrintAgent\Sdk\Facades\PrintAgent;

$receipt = ReceiptBuilder::make()
    ->store('Acme Diner', ['123 Main St'])
    ->item('Cheeseburger', 2, 8.50)
    ->total(17.00)
    ->footer('Thank you!')
    ->cut();

$printer = PrintAgent::printers()->list()[0];

PrintAgent::jobs()->print('document', $receipt->toJson(), printerId: $printer->id);
```

## Documentation

- [Installation Guide](docs/installation.md)
- [Configuration Guide](docs/configuration.md)
- [Quick Start](docs/quick-start.md)
- [Printing a Receipt](docs/printing-receipt.md)
- [Printing a Kitchen Ticket](docs/printing-kitchen-ticket.md)
- [Printer Discovery](docs/printer-discovery.md)
- [Queue Management](docs/queue-management.md)
- [Error Handling](docs/error-handling.md)
- [Example Laravel usage](examples/)

## Package structure

```
src/
  Client/         PrintAgentClient — the single HTTP client bound as a singleton
  Contracts/      PrintAgentClientContract — what every Resource depends on (mockable in tests)
  Resources/      PrinterResource, JobResource, QueueResource, HealthResource
  Builders/       DocumentBuilder (generic), ReceiptBuilder, KitchenTicketBuilder, LabelBuilder
  DTO/            Printer, PrintJob, Health, Metrics, QueueStatus, Configuration, PaginatedResult
    Enums/        Typed enums mirroring the agent's own TypeScript enums exactly
    Health/       Nested value objects (MemoryUsage, CpuUsage, DiskUsage, ...)
  Exceptions/     ApiException, ConnectionException, PrinterOfflineException, ValidationException,
                  QueueException, TimeoutException
  Facades/        PrintAgent
  Providers/      PrintAgentServiceProvider
  Support/        ResponseParser, helpers.php (print_agent())
config/           print-agent.php
routes/           print-agent.php (optional, opt-in proxy routes)
tests/            Pest — Unit (builders, DTOs) + Feature (resources, facade, provider)
docs/             The eight guides linked above
examples/         Drop-in Laravel controller/command examples
```

## Developer experience

- **Dependency injection**: type-hint `PrintAgentClientContract` anywhere
- **Facade**: `PrintAgent::printers()->list()`
- **Helper function**: `print_agent()->health()->health()`
- **Fluent builders**: `DocumentBuilder`, `ReceiptBuilder`, `KitchenTicketBuilder`, `LabelBuilder`
- **IDE autocomplete**: the facade carries full `@method` docblocks; every DTO and enum is a real
  typed class, not an associative array
- **Static analysis**: ships with a `phpstan.neon` (Larastan, level 8) — the package itself
  passes with zero errors

## Testing

```bash
composer test      # Pest — 46 tests, 106 assertions
composer analyse    # PHPStan/Larastan level 8
composer check      # both
```

The test suite mocks Laravel's HTTP client (`Http::fake()`) — no real Print Agent instance is
needed to run it. `tests/Unit/Builders/DocumentBuilderTest.php` in particular verifies the
generated JSON byte-for-byte matches what the agent's own document engine expects (including
easy-to-miss details like an empty style object serializing as `{}`, not `[]`).

## Installing today vs. publishing to Packagist later

Currently installed via the git VCS repository method (see [Installation](#installation) above)
since the repo at `github.com/rahulchaudhary2002/print-agent-php` is private — a `v1.0.0` tag is
already pushed for consumers to pin to.

If this is ever made public, publishing to Packagist properly is just:

1. Make the GitHub repo public.
2. Submit it at [packagist.org/packages/submit](https://packagist.org/packages/submit).
3. Enable the GitHub webhook Packagist provides so future tags publish automatically.
4. Consumers can then drop the `repositories` block and just run
   `composer require print-agent/print-agent-php`.

## License

MIT
