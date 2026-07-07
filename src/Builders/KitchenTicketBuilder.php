<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Builders;

use Carbon\CarbonImmutable;
use Carbon\CarbonInterface;
use PrintAgent\Sdk\DTO\Enums\CharacterSize;
use PrintAgent\Sdk\DTO\Enums\CutType;

/**
 * A higher-level abstraction over {@see DocumentBuilder} for kitchen/expo tickets — order
 * number, table, line items with modifiers/notes, and a timestamp, printed large and
 * unambiguous for a kitchen environment rather than styled like a customer-facing receipt.
 *
 * @example
 * $ticket = KitchenTicketBuilder::make()
 *     ->orderNumber('1042')
 *     ->table('7')
 *     ->item('Cheeseburger', 2, ['No onions', 'Extra cheese'])
 *     ->item('Fries', 1)
 *     ->note('Rush — customer waiting')
 *     ->cut();
 */
final class KitchenTicketBuilder
{
    private DocumentBuilder $document;

    private function __construct()
    {
        $this->document = DocumentBuilder::make();
    }

    public static function make(): self
    {
        return new self;
    }

    public function orderNumber(string $number): self
    {
        $this->document->center("ORDER #{$number}", ['bold' => true, 'characterSize' => CharacterSize::DoubleWidthHeight]);

        return $this;
    }

    public function table(string $tableNumber): self
    {
        $this->document->center("Table {$tableNumber}", ['bold' => true]);

        return $this;
    }

    public function time(?CarbonInterface $time = null): self
    {
        $this->document->center(($time ?? CarbonImmutable::now())->format('Y-m-d H:i:s'));
        $this->document->line('=');

        return $this;
    }

    /** @param array<int, string> $modifiers */
    public function item(string $name, int $quantity = 1, array $modifiers = [], ?string $notes = null): self
    {
        $this->document->bold("{$quantity}x {$name}", ['characterSize' => CharacterSize::DoubleHeight]);
        foreach ($modifiers as $modifier) {
            $this->document->text("  - {$modifier}");
        }
        if ($notes !== null) {
            $this->document->text("  * {$notes}");
        }
        $this->document->feed(1);

        return $this;
    }

    public function note(string $text): self
    {
        $this->document->line('*');
        $this->document->bold($text);
        $this->document->line('*');

        return $this;
    }

    public function feed(int $lines = 1): self
    {
        $this->document->feed($lines);

        return $this;
    }

    public function cut(CutType $type = CutType::Full): self
    {
        $this->document->cut($type);

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        return $this->document->toArray();
    }

    public function toJson(): string
    {
        return $this->document->toJson();
    }

    public function document(): DocumentBuilder
    {
        return $this->document;
    }
}
