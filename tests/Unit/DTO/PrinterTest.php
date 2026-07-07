<?php

declare(strict_types=1);

use PrintAgent\Sdk\DTO\Enums\PrinterStatus;
use PrintAgent\Sdk\DTO\Printer;

it('hydrates from the agent envelope shape', function () {
    $printer = Printer::fromArray([
        'id' => 'abc-123',
        'name' => 'Kitchen',
        'driver' => 'escpos-usb',
        'connectionType' => 'usb',
        'connection' => ['vendorId' => 1046, 'productId' => 8214],
        'status' => 'online',
        'isDefault' => true,
        'enabled' => true,
        'createdAt' => '2026-01-01T00:00:00.000Z',
        'updatedAt' => '2026-01-02T00:00:00.000Z',
    ]);

    expect($printer->id)->toBe('abc-123');
    expect($printer->status)->toBe(PrinterStatus::Online);
    expect($printer->isDefault)->toBeTrue();
    expect($printer->createdAt->toDateString())->toBe('2026-01-01');
});
