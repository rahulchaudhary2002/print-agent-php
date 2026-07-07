<?php

declare(strict_types=1);

use PrintAgent\Sdk\Builders\ReceiptBuilder;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;

it('builds a full receipt end to end', function () {
    $document = ReceiptBuilder::make()
        ->store('Acme Diner', ['123 Main St', 'Springfield'])
        ->item('Cheeseburger', 2, 8.50)
        ->item('Fries', 1, 3.00)
        ->subtotal(20.00)
        ->discount('Loyalty', 2.00)
        ->tax('VAT', 1.80)
        ->total(19.80)
        ->footer('Thank you for visiting!')
        ->qrCode('https://example.test/receipt/1001')
        ->barcode(BarcodeType::Code128, 'ORDER1001')
        ->openCashDrawer()
        ->cut()
        ->toArray();

    $allTypes = collect($document['sections'][0]['elements'])->pluck('type')->all();

    expect($allTypes)->toContain('text', 'divider', 'table', 'qrcode', 'barcode', 'drawer', 'cut', 'feed');
});

it('renders a discount as a negative amount', function () {
    $document = ReceiptBuilder::make()->discount('Loyalty', 2.00)->toArray();
    $row = $document['sections'][0]['elements'][0]['rows'][0]['cells'];

    expect($row)->toBe(['Discount (Loyalty)', '-2.00']);
});

it('bolds the total row only', function () {
    $document = ReceiptBuilder::make()->subtotal(10.00)->total(10.00)->toArray();
    $elements = $document['sections'][0]['elements'];

    // subtotal table has no divider before it; total() adds a divider then a bold row.
    $subtotalTable = $elements[0];
    $totalTable = $elements[2];

    expect($subtotalTable['rows'][0])->not->toHaveKey('style');
    expect($totalTable['rows'][0]['style'])->toBe(['bold' => true]);
});

it('exposes the underlying DocumentBuilder as an escape hatch', function () {
    $builder = ReceiptBuilder::make();
    $builder->document()->text('custom line');

    expect($builder->toArray()['sections'][0]['elements'][0]['content'])->toBe('custom line');
});
