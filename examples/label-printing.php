<?php

declare(strict_types=1);

// Example: printing a shipping label for an order.

use App\Models\Shipment;
use PrintAgent\Sdk\Builders\LabelBuilder;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;
use PrintAgent\Sdk\Facades\PrintAgent;

function printShippingLabel(Shipment $shipment, string $labelPrinterId): void
{
    $label = LabelBuilder::make(widthMm: 100, heightMm: 150)
        ->text($shipment->carrier, ['bold' => true])
        ->text($shipment->trackingNumber)
        ->center($shipment->recipientName)
        ->text($shipment->recipientAddressLine1)
        ->text($shipment->recipientAddressLine2)
        ->barcode(BarcodeType::Code128, $shipment->trackingNumber)
        ->qrCode($shipment->trackingUrl);

    PrintAgent::jobs()->print('document', $label->toJson(), printerId: $labelPrinterId);
}
