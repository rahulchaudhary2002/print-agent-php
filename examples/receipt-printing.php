<?php

declare(strict_types=1);

// Example: POST /orders/{order}/print-receipt

use App\Models\Order;
use Illuminate\Http\RedirectResponse;
use PrintAgent\Sdk\Builders\ReceiptBuilder;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;
use PrintAgent\Sdk\Exceptions\PrintAgentException;
use PrintAgent\Sdk\Facades\PrintAgent;

final class PrintReceiptController
{
    public function __invoke(Order $order): RedirectResponse
    {
        $receipt = ReceiptBuilder::make()
            ->store($order->store->name, [$order->store->address])
            ->heading("Order #{$order->number}");

        foreach ($order->items as $item) {
            $receipt->item($item->name, $item->quantity, $item->unit_price);
        }

        $receipt
            ->subtotal($order->subtotal)
            ->tax('Sales Tax', $order->tax)
            ->total($order->total)
            ->footer('Thank you for your order!')
            ->barcode(BarcodeType::Code128, (string) $order->number)
            ->cut();

        try {
            $job = PrintAgent::jobs()->print(
                type: 'document',
                payload: $receipt->toJson(),
                printerId: $order->store->default_printer_id,
            );
        } catch (PrintAgentException $e) {
            report($e);

            return back()->with('error', "Could not print receipt: {$e->getMessage()}");
        }

        return back()->with('status', "Receipt queued as job {$job->id}");
    }
}
