<?php

declare(strict_types=1);

// Example: an A4/letter-oriented invoice using the generic DocumentBuilder directly —
// ReceiptBuilder is tuned for narrow thermal paper, so a wider invoice layout is built by hand.

use PrintAgent\Sdk\Builders\DocumentBuilder;
use PrintAgent\Sdk\DTO\Enums\Alignment;
use PrintAgent\Sdk\Facades\PrintAgent;

function printInvoice(object $invoice, string $printerId): \PrintAgent\Sdk\DTO\PrintJob
{
    $document = DocumentBuilder::make()
        ->center("INVOICE #{$invoice->number}", ['bold' => true])
        ->text("Date: {$invoice->date}")
        ->text("Bill to: {$invoice->customerName}")
        ->line('=', 64);

    $document->table(
        columns: [
            ['header' => 'Description', 'width' => 34],
            ['header' => 'Qty', 'width' => 6, 'align' => Alignment::Right],
            ['header' => 'Price', 'width' => 10, 'align' => Alignment::Right],
            ['header' => 'Total', 'width' => 10, 'align' => Alignment::Right],
        ],
        rows: array_map(
            fn (object $line) => [
                $line->description,
                (string) $line->quantity,
                number_format($line->unitPrice, 2),
                number_format($line->quantity * $line->unitPrice, 2),
            ],
            $invoice->lines,
        ),
    );

    $document->line('-', 64)->text("Total due: {$invoice->currency} " . number_format($invoice->total, 2), ['align' => Alignment::Right, 'bold' => true]);
    $document->feed(2)->cut();

    return PrintAgent::jobs()->print('document', $document->toJson(), printerId: $printerId);
}
