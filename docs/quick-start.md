# Quick Start

## Check the agent is reachable

```php
use PrintAgent\Sdk\Facades\PrintAgent;

if (! PrintAgent::testConnection()) {
    abort(503, 'Print Agent is not reachable');
}
```

## List printers

```php
$printers = PrintAgent::printers()->list();

foreach ($printers as $printer) {
    echo "{$printer->name} ({$printer->status->value})\n";
}
```

## Print a simple document

```php
use PrintAgent\Sdk\Builders\DocumentBuilder;

$document = DocumentBuilder::make()
    ->center('Hello, World!')
    ->feed()
    ->cut();

$job = PrintAgent::jobs()->print(
    type: 'document',
    payload: $document->toJson(),
    printerId: $printers[0]->id,
);

echo "Job {$job->id} is {$job->status->value}\n";
```

## Print a receipt (higher-level builder)

```php
use PrintAgent\Sdk\Builders\ReceiptBuilder;

$receipt = ReceiptBuilder::make()
    ->store('Acme Diner', ['123 Main St'])
    ->item('Cheeseburger', 2, 8.50)
    ->total(17.00)
    ->footer('Thank you!')
    ->cut();

PrintAgent::jobs()->print('document', $receipt->toJson(), printerId: $printers[0]->id);
```

## Using dependency injection instead of the facade

```php
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;

class PrintReceiptController
{
    public function __construct(private readonly PrintAgentClientContract $printAgent) {}

    public function __invoke()
    {
        return $this->printAgent->health()->health();
    }
}
```

## Using the helper function

```php
print_agent()->printers()->list();
```

See [printing-receipt.md](printing-receipt.md), [printing-kitchen-ticket.md](printing-kitchen-ticket.md),
[printer-discovery.md](printer-discovery.md), [queue-management.md](queue-management.md), and
[error-handling.md](error-handling.md) for the rest.
