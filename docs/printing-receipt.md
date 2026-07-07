# Printing a Receipt

`ReceiptBuilder` is a higher-level abstraction over the generic `DocumentBuilder`, covering the
common store-receipt shape: header, line items, discount/tax/total breakdown, footer, and the
usual finishing commands.

```php
use PrintAgent\Sdk\Builders\ReceiptBuilder;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;
use PrintAgent\Sdk\Facades\PrintAgent;

$receipt = ReceiptBuilder::make(paperColumns: 48) // 32/48/64 ≈ 58mm/80mm/custom-wide
    ->store('Acme Diner', ['123 Main St', 'Springfield'], phone: '555-0100')
    ->item('Cheeseburger', quantity: 2, unitPrice: 8.50)
    ->item('Fries', quantity: 1, unitPrice: 3.00)
    ->subtotal(20.00)
    ->discount('Loyalty', 2.00)
    ->tax('VAT', 1.80)
    ->total(19.80)
    ->footer('Thank you for visiting!')
    ->qrCode('https://example.com/receipt/1001')
    ->barcode(BarcodeType::Code128, 'ORDER1001')
    ->openCashDrawer()
    ->cut();

PrintAgent::jobs()->print(
    type: 'document',
    payload: $receipt->toJson(),
    printerId: $defaultPrinter->id,
);
```

## What each method does

| Method | Produces |
|---|---|
| `store($name, $addressLines, $phone)` | Large centered bold store name, address lines, a `=` divider |
| `heading($text)` | A centered bold line — for section headers within the receipt |
| `item($name, $qty, $unitPrice)` | A two-column row: `"{name} x{qty}"` left, formatted total right |
| `subtotal`/`discount`/`tax`/`total` | Two-column total rows; `total()` adds a divider first and bolds the row; `discount()` shows a negative amount |
| `footer($text)` | A feed line then centered text |
| `qrCode`/`barcode` | Delegates straight to `DocumentBuilder`, centered by default in this builder |
| `openCashDrawer($pin)` | Cash drawer kick pulse |
| `cut($type)` | `CutType::Full` (default) or `CutType::Partial` |

## Escaping to the generic builder

Anything `ReceiptBuilder` doesn't expose is still reachable via the underlying `DocumentBuilder`:

```php
$receipt->document()->image('/path/to/logo.png');
```

## Column widths

`paperColumns` defaults to 48 (a reasonable middle ground). It only affects where the divider
lines end and where the right-aligned total column sits — it does not need to exactly match the
physical paper width; the agent's renderer wraps to the actual printer's paper profile
regardless.
