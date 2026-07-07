<?php

declare(strict_types=1);

// Example: printing a kitchen ticket to a specific printer when an order is placed.

use App\Events\OrderPlaced;
use PrintAgent\Sdk\Builders\KitchenTicketBuilder;
use PrintAgent\Sdk\Facades\PrintAgent;

final class PrintKitchenTicketListener
{
    public function handle(OrderPlaced $event): void
    {
        $order = $event->order;

        $ticket = KitchenTicketBuilder::make()
            ->orderNumber((string) $order->number)
            ->table((string) $order->tableNumber)
            ->time();

        foreach ($order->items as $item) {
            $ticket->item($item->name, $item->quantity, $item->modifiers, $item->notes);
        }

        if ($order->isRush) {
            $ticket->note('RUSH — customer waiting');
        }

        $ticket->cut();

        // Kitchen tickets go to the kitchen printer specifically, never the customer-facing one.
        PrintAgent::jobs()->print('document', $ticket->toJson(), printerId: config('services.print_agent.kitchen_printer_id'));
    }
}
