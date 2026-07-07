# Example Laravel Usage

These are drop-in snippets for an existing Laravel app (controllers/routes), not a scaffolded
second application — the SDK itself has no opinion on your app's structure, and shipping a full
example Laravel project would duplicate a large amount of boilerplate this repository doesn't
need to own or keep in sync. Each file below is a single, focused example matching one of the
scenarios from Step 19.

- [`receipt-printing.php`](receipt-printing.php) — `ReceiptBuilder` end to end
- [`invoice-printing.php`](invoice-printing.php) — a generic `DocumentBuilder` invoice using tables
- [`kitchen-printing.php`](kitchen-printing.php) — `KitchenTicketBuilder`, targeting a specific printer
- [`label-printing.php`](label-printing.php) — `LabelBuilder` for a shipping label
- [`queue-management.php`](queue-management.php) — pausing/resuming/inspecting the queue from an Artisan command
- [`health-check.php`](health-check.php) — a Laravel health-check integration

Each file is a complete, copy-pasteable controller method or command — replace the class/route
wiring with whatever fits your app.
