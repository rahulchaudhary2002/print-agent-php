# Printing a Kitchen Ticket

`KitchenTicketBuilder` favors large, unambiguous text over the receipt's compact layout — an
order number, table, line items with modifiers/notes, and a timestamp.

```php
use PrintAgent\Sdk\Builders\KitchenTicketBuilder;
use PrintAgent\Sdk\Facades\PrintAgent;

$ticket = KitchenTicketBuilder::make()
    ->orderNumber('1042')
    ->table('7')
    ->time() // defaults to now(); pass a Carbon instance to override
    ->item('Cheeseburger', quantity: 2, modifiers: ['No onions', 'Extra cheese'])
    ->item('Fries', quantity: 1, notes: 'Extra crispy')
    ->note('RUSH — customer waiting')
    ->cut();

PrintAgent::jobs()->print('document', $ticket->toJson(), printerId: $kitchenPrinter->id);
```

## What each method does

| Method | Produces |
|---|---|
| `orderNumber($number)` | Large, bold, centered `ORDER #{number}` |
| `table($number)` | Centered bold `Table {number}` |
| `time($carbon = null)` | Centered timestamp (`Y-m-d H:i:s`) followed by a `=` divider |
| `item($name, $qty, $modifiers, $notes)` | Bold double-height `{qty}x {name}`, each modifier indented with `-`, notes indented with `*` |
| `note($text)` | Bold text framed by `*` dividers — for anything that needs to stand out (rush orders, allergies) |

Sending a kitchen ticket to a *different* printer than the customer receipt is just a matter of
passing a different `printerId` — see [printer-discovery.md](printer-discovery.md) for finding
the right printer to target.
