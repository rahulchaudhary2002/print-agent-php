<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Builders;

use PrintAgent\Sdk\DTO\Enums\Alignment;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;
use PrintAgent\Sdk\DTO\Enums\CharacterSize;
use PrintAgent\Sdk\DTO\Enums\CutType;
use PrintAgent\Sdk\DTO\Enums\QrErrorCorrection;

/**
 * A higher-level, opinionated abstraction over {@see DocumentBuilder} for the common
 * store-receipt shape: header, line items, discount/tax/total breakdown, footer, and the usual
 * finishing commands (QR/barcode, cash drawer, cut). Every method still just assembles the same
 * plain JSON document — nothing here talks to a printer or renders ESC/POS.
 *
 * @example
 * $document = ReceiptBuilder::make()
 *     ->store('Acme Diner', ['123 Main St', 'Springfield'])
 *     ->item('Cheeseburger', 2, 8.50)
 *     ->item('Fries', 1, 3.00)
 *     ->subtotal(20.00)
 *     ->discount('Loyalty', 2.00)
 *     ->tax('VAT', 1.80)
 *     ->total(19.80)
 *     ->footer('Thank you for visiting!')
 *     ->cut()
 *     ->toArray();
 */
final class ReceiptBuilder
{
    private DocumentBuilder $document;

    private function __construct(private readonly int $paperColumns)
    {
        $this->document = DocumentBuilder::make();
    }

    /** @param 32|48|64 $paperColumns matches 58mm/72mm/80mm at the agent's default font */
    public static function make(int $paperColumns = 48): self
    {
        return new self($paperColumns);
    }

    /** @param array<int, string> $addressLines */
    public function store(string $name, array $addressLines = [], ?string $phone = null): self
    {
        $this->document->center($name, ['bold' => true, 'characterSize' => CharacterSize::DoubleWidthHeight]);
        foreach ($addressLines as $line) {
            $this->document->center($line);
        }
        if ($phone !== null) {
            $this->document->center($phone);
        }
        $this->document->line('=', $this->paperColumns);

        return $this;
    }

    public function heading(string $text): self
    {
        $this->document->center($text, ['bold' => true]);

        return $this;
    }

    public function line(string $character = '-'): self
    {
        $this->document->line($character, $this->paperColumns);

        return $this;
    }

    public function text(string $text): self
    {
        $this->document->text($text);

        return $this;
    }

    /** One line item as a two-column row: description on the left, formatted total on the right. */
    public function item(string $name, int $quantity, float $unitPrice): self
    {
        $label = $quantity > 1 ? "{$name} x{$quantity}" : $name;

        return $this->row($label, $quantity * $unitPrice);
    }

    public function subtotal(float $amount): self
    {
        return $this->row('Subtotal', $amount);
    }

    public function discount(string $label, float $amount): self
    {
        return $this->row("Discount ({$label})", -$amount);
    }

    public function tax(string $label, float $amount): self
    {
        return $this->row("Tax ({$label})", $amount);
    }

    public function total(float $amount): self
    {
        $this->document->line('-', $this->paperColumns);

        return $this->row('TOTAL', $amount, bold: true);
    }

    private function row(string $label, float $amount, bool $bold = false): self
    {
        $this->document->table(
            columns: [
                ['width' => $this->paperColumns - 10],
                ['width' => 10, 'align' => Alignment::Right],
            ],
            rows: [[$label, number_format($amount, 2)]],
            rowStyles: $bold ? [0 => ['bold' => true]] : [],
        );

        return $this;
    }

    public function footer(string $text): self
    {
        $this->document->feed(1)->center($text);

        return $this;
    }

    public function qrCode(string $content, int $size = 4): self
    {
        $this->document->qrCode($content, size: $size, errorCorrection: QrErrorCorrection::Medium);

        return $this;
    }

    public function barcode(BarcodeType $type, string $value): self
    {
        $this->document->barcode($type, $value);

        return $this;
    }

    public function openCashDrawer(int $pin = 0): self
    {
        $this->document->drawer($pin);

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

    /** Escape hatch to the underlying generic builder for anything this abstraction doesn't cover. */
    public function document(): DocumentBuilder
    {
        return $this->document;
    }
}
