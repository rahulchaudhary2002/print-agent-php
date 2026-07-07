<?php

declare(strict_types=1);

use PrintAgent\Sdk\Builders\DocumentBuilder;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;
use PrintAgent\Sdk\DTO\Enums\CutType;
use PrintAgent\Sdk\Exceptions\ValidationException;

it('builds the exact PrintDocument shape the agent expects', function () {
    $document = DocumentBuilder::make()
        ->center('Restaurant')
        ->line()
        ->text('Invoice #1001')
        ->feed()
        ->cut()
        ->toArray();

    expect($document)->toHaveKey('sections');
    expect($document['sections'])->toHaveCount(1);

    $elements = $document['sections'][0]['elements'];
    expect($elements)->toHaveCount(5);

    expect($elements[0]['type'])->toBe('text');
    expect($elements[0]['content'])->toBe('Restaurant');
    expect($elements[0]['style'])->toEqual(['align' => 'center']);

    expect($elements[1]['type'])->toBe('divider');
    expect($elements[1])->not->toHaveKey('align');

    expect($elements[2]['type'])->toBe('text');
    expect($elements[2]['content'])->toBe('Invoice #1001');
    expect($elements[2]['style'])->toEqual(new stdClass);

    expect($elements[3]['type'])->toBe('feed');
});

it('never includes a style key on feed/cut/drawer elements', function () {
    $document = DocumentBuilder::make()->feed()->cut()->drawer()->toArray();
    $elements = $document['sections'][0]['elements'];

    foreach ($elements as $element) {
        expect($element)->not->toHaveKey('style');
    }
});

it('encodes an empty style object as {} not [] in JSON', function () {
    $json = DocumentBuilder::make()->line()->toJson();

    expect($json)->toContain('"style":{}');
    expect($json)->not->toContain('"style":[]');
});

it('drops the sections/document style and metadata keys entirely when unset', function () {
    $document = DocumentBuilder::make()->text('hello')->toArray();

    expect($document)->not->toHaveKey('style');
    expect($document)->not->toHaveKey('metadata');
    expect($document['sections'][0])->not->toHaveKey('style');
});

it('includes metadata when explicitly set', function () {
    $document = DocumentBuilder::make()->metadata(['label' => ['widthMm' => 50]])->text('x')->toArray();

    expect($document['metadata'])->toBe(['label' => ['widthMm' => 50]]);
});

it('rejects a divider character longer than one character', function () {
    DocumentBuilder::make()->line('--');
})->throws(ValidationException::class);

it('rejects a table whose column widths exceed 64', function () {
    DocumentBuilder::make()->table(
        columns: [['width' => 40], ['width' => 30]],
        rows: [['a', 'b']],
    );
})->throws(ValidationException::class, 'exceeds the 64-character maximum');

it('rejects a table row with the wrong number of cells', function () {
    DocumentBuilder::make()->table(
        columns: [['width' => 10], ['width' => 10]],
        rows: [['only-one-cell']],
    );
})->throws(ValidationException::class);

it('supports per-row style overrides on a table', function () {
    $document = DocumentBuilder::make()
        ->table(
            columns: [['width' => 20], ['width' => 10]],
            rows: [['Subtotal', '10.00'], ['TOTAL', '10.00']],
            rowStyles: [1 => ['bold' => true]],
        )
        ->toArray();

    $table = $document['sections'][0]['elements'][0];
    expect($table['rows'][0])->not->toHaveKey('style');
    expect($table['rows'][1]['style'])->toEqual(['bold' => true]);
});

it('validates barcode values against the type-specific pattern', function () {
    DocumentBuilder::make()->barcode(BarcodeType::Ean13, 'not-a-valid-ean13');
})->throws(ValidationException::class);

it('accepts a valid EAN13 barcode value', function () {
    $document = DocumentBuilder::make()->barcode(BarcodeType::Ean13, '5901234123457')->toArray();
    $barcode = $document['sections'][0]['elements'][0];

    expect($barcode['barcodeType'])->toBe('EAN13');
    expect($barcode['value'])->toBe('5901234123457');
});

it('rejects a QR code size outside 1-16', function () {
    DocumentBuilder::make()->qrCode('hello', size: 20);
})->throws(ValidationException::class);

it('rejects a cash drawer duration outside 1-510ms', function () {
    DocumentBuilder::make()->drawer(durationMs: 1000);
})->throws(ValidationException::class);

it('refuses to build an empty document', function () {
    DocumentBuilder::make()->toArray();
})->throws(ValidationException::class);

it('supports multiple sections', function () {
    $document = DocumentBuilder::make()
        ->text('header')
        ->newSection()
        ->text('body')
        ->toArray();

    expect($document['sections'])->toHaveCount(2);
    expect($document['sections'][0]['elements'][0]['content'])->toBe('header');
    expect($document['sections'][1]['elements'][0]['content'])->toBe('body');
});

it('produces a cut element with the requested cut type', function () {
    $document = DocumentBuilder::make()->text('x')->cut(CutType::Partial)->toArray();
    $cut = $document['sections'][0]['elements'][1];

    expect($cut)->toBe(['type' => 'cut', 'cutType' => 'partial']);
});
