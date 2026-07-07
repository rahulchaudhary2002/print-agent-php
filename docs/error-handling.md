# Error Handling

## Exception hierarchy

Every exception this SDK throws extends `PrintAgentException` — catch that as a blanket handler,
or catch a specific subtype for finer control:

```
PrintAgentException (base)
├── ApiException            the agent responded with {"success": false, ...}
├── ConnectionException     the agent could not be reached at all
├── TimeoutException        the request exceeded the configured timeout
├── PrinterOfflineException thrown by your own code after checking printer status
├── ValidationException     thrown client-side, before any request is sent
└── QueueException          available for your own code to classify queue-full/paused failures
```

## Typical handling

```php
use PrintAgent\Sdk\Exceptions\ApiException;
use PrintAgent\Sdk\Exceptions\ConnectionException;
use PrintAgent\Sdk\Exceptions\TimeoutException;
use PrintAgent\Sdk\Exceptions\ValidationException;

try {
    $job = PrintAgent::jobs()->print('document', $document->toJson(), printerId: $printer->id);
} catch (ValidationException $e) {
    // Bad input caught before any HTTP request was made — e.g. an invalid barcode value.
    report($e);
    return back()->withErrors($e->errors ?: [$e->getMessage()]);
} catch (ApiException $e) {
    // The agent rejected the request — inspect $e->statusCode and $e->errors for detail.
    report($e);
    return back()->withErrors($e->errors);
} catch (ConnectionException|TimeoutException $e) {
    // The agent is unreachable or slow to respond — likely not running.
    report($e);
    return back()->with('error', 'Print Agent is currently unavailable.');
}
```

## What's validated where

`ValidationException` is thrown by the **Builders** before a request is ever sent — for things
the agent's own document engine would otherwise silently render wrong (an invalid barcode
value for its type, a table wider than 64 columns, an out-of-range QR size). The agent itself
still validates request *shape* (via its own Zod schemas) independently — a `422`/`400`
response from the agent surfaces as `ApiException`, not `ValidationException`, since that
happened server-side.

## Retries

Connection-level failures (not 4xx/5xx responses) are retried automatically per the `retry`
config (default: 2 retries, 100ms apart) before a `ConnectionException`/`TimeoutException` is
ever thrown to your code — see [configuration.md](configuration.md).
