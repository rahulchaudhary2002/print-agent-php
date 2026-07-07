<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use PrintAgent\Sdk\Contracts\PrintAgentClientContract;
use PrintAgent\Sdk\DTO\Printer;
use PrintAgent\Sdk\Exceptions\ApiException;

function fakePrinter(array $overrides = []): array
{
    return array_merge([
        'id' => 'printer-1',
        'name' => 'Kitchen',
        'driver' => 'escpos-usb',
        'connectionType' => 'usb',
        'connection' => [],
        'status' => 'online',
        'isDefault' => false,
        'enabled' => true,
        'createdAt' => '2026-01-01T00:00:00.000Z',
        'updatedAt' => '2026-01-01T00:00:00.000Z',
    ], $overrides);
}

it('lists printers', function () {
    Http::fake([
        '*/printers' => Http::response(['success' => true, 'message' => 'Success', 'data' => [fakePrinter()]], 200),
    ]);

    $printers = app(PrintAgentClientContract::class)->printers()->list();

    expect($printers)->toHaveCount(1);
    expect($printers[0])->toBeInstanceOf(Printer::class);
    expect($printers[0]->name)->toBe('Kitchen');
});

it('creates a printer', function () {
    Http::fake([
        '*/printers' => Http::response(['success' => true, 'message' => 'Printer created', 'data' => fakePrinter(['name' => 'New Printer'])], 201),
    ]);

    $printer = app(PrintAgentClientContract::class)->printers()->create(
        name: 'New Printer',
        driver: 'escpos-usb',
        connectionType: 'usb',
        connection: ['vendorId' => 1, 'productId' => 2],
    );

    expect($printer->name)->toBe('New Printer');

    Http::assertSent(function ($request) {
        return $request->url() === 'http://127.0.0.1:3210/api/v1/printers'
            && $request['name'] === 'New Printer'
            && $request['driver'] === 'escpos-usb';
    });
});

it('throws ApiException when the agent rejects the request', function () {
    Http::fake([
        '*/printers/missing' => Http::response(['success' => false, 'message' => 'Printer missing not found', 'errors' => ['Printer missing not found']], 404),
    ]);

    app(PrintAgentClientContract::class)->printers()->get('missing');
})->throws(ApiException::class, 'Printer missing not found');

it('sets a printer as default', function () {
    Http::fake([
        '*/printers/printer-1/default' => Http::response(['success' => true, 'message' => 'Default printer updated', 'data' => fakePrinter(['isDefault' => true])], 200),
    ]);

    $printer = app(PrintAgentClientContract::class)->printers()->setDefault('printer-1');

    expect($printer->isDefault)->toBeTrue();
});

it('discovers printers without wrapping them in a DTO', function () {
    Http::fake([
        '*/printers/discover' => Http::response(['success' => true, 'message' => 'Success', 'data' => [
            ['name' => 'USB Printer 0483:5743', 'connection' => 'USB', 'driver' => 'escpos-usb'],
        ]], 200),
    ]);

    $discovered = app(PrintAgentClientContract::class)->printers()->discover();

    expect($discovered)->toBe([
        ['name' => 'USB Printer 0483:5743', 'connection' => 'USB', 'driver' => 'escpos-usb'],
    ]);
});
