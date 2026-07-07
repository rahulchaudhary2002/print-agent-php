# Printer Discovery

## Listing registered printers

```php
use PrintAgent\Sdk\Facades\PrintAgent;

$printers = PrintAgent::printers()->list();
$default = collect($printers)->first(fn ($p) => $p->isDefault);
```

## Discovering new hardware

`discover()` triggers the agent's own USB/network/Windows/CUPS scanners and returns raw
candidates — deliberately not a typed DTO, since the shape varies per transport:

```php
$candidates = PrintAgent::printers()->discover();

foreach ($candidates as $candidate) {
    echo "{$candidate['name']} via {$candidate['driver']} ({$candidate['connection']})\n";
}
```

Discovery only reports candidates; it never registers them. To actually add one:

```php
$printer = PrintAgent::printers()->create(
    name: 'Kitchen Printer',
    driver: 'escpos-usb',
    connectionType: 'usb',
    connection: ['vendorId' => 1046, 'productId' => 8214],
    isDefault: false,
);
```

## Checking live status

```php
$status = PrintAgent::printers()->status($printer->id); // live diagnostics: connection, health score, metrics
$result = PrintAgent::printers()->test($printer->id);   // sends a real test print
```

## Managing printers

```php
PrintAgent::printers()->update($printer->id, ['name' => 'Front Counter']);
PrintAgent::printers()->setDefault($printer->id);
PrintAgent::printers()->disable($printer->id); // blocks new jobs without deleting it
PrintAgent::printers()->enable($printer->id);
PrintAgent::printers()->delete($printer->id);
```

## Handling an offline printer

```php
use PrintAgent\Sdk\Exceptions\PrinterOfflineException;

$status = PrintAgent::printers()->status($printer->id);
if (($status['status'] ?? null) === 'offline') {
    throw new PrinterOfflineException($printer->id);
}
```

The SDK itself doesn't pre-check printer status before submitting a job (that's the agent's
job, and doing it twice would be redundant business logic) — `PrinterOfflineException` is
provided for your own application code to use when you've already checked status and want a
typed exception to catch, as shown above.
