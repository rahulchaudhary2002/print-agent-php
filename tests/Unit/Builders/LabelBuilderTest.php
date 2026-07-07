<?php

declare(strict_types=1);

use PrintAgent\Sdk\Builders\LabelBuilder;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;

it('records label dimensions as document metadata', function () {
    $document = LabelBuilder::make(widthMm: 50, heightMm: 30)->text('SKU-1')->toArray();

    expect($document['metadata'])->toBe(['label' => ['widthMm' => 50.0, 'heightMm' => 30.0]]);
});

it('builds a label with text, barcode, and qr code', function () {
    $document = LabelBuilder::make(50, 30)
        ->text('SKU-10293')
        ->barcode(BarcodeType::Code128, '10293004421')
        ->qrCode('https://example.test/sku/10293')
        ->toArray();

    $types = array_column($document['sections'][0]['elements'], 'type');

    expect($types)->toBe(['text', 'barcode', 'qrcode']);
});
