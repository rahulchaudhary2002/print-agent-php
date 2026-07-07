<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Builders;

use PrintAgent\Sdk\DTO\Enums\Alignment;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;
use PrintAgent\Sdk\DTO\Enums\ImageFormat;

/**
 * A higher-level abstraction over {@see DocumentBuilder} for small-format labels (shipping,
 * product, shelf-edge) — text, a barcode/QR code, and an optional image, with an explicit
 * physical size recorded as document metadata for the agent/driver layer to use if it cares.
 *
 * @example
 * $label = LabelBuilder::make(widthMm: 50, heightMm: 30)
 *     ->text('SKU-10293')
 *     ->barcode(BarcodeType::Code128, '10293004421')
 *     ->toArray();
 */
final class LabelBuilder
{
    private DocumentBuilder $document;

    private function __construct(float $widthMm, float $heightMm)
    {
        $this->document = DocumentBuilder::make()->metadata([
            'label' => ['widthMm' => $widthMm, 'heightMm' => $heightMm],
        ]);
    }

    public static function make(float $widthMm, float $heightMm): self
    {
        return new self($widthMm, $heightMm);
    }

    /** @param array<string, mixed> $style */
    public function text(string $text, array $style = []): self
    {
        $this->document->text($text, $style);

        return $this;
    }

    public function center(string $text): self
    {
        $this->document->center($text);

        return $this;
    }

    public function barcode(BarcodeType $type, string $value, bool $showText = true): self
    {
        $this->document->barcode($type, $value, showText: $showText, style: ['align' => Alignment::Center]);

        return $this;
    }

    public function qrCode(string $content, int $size = 4): self
    {
        $this->document->qrCode($content, size: $size, style: ['align' => Alignment::Center]);

        return $this;
    }

    public function image(string $path, ?ImageFormat $format = null, ?int $width = null, ?int $height = null): self
    {
        $this->document->image($path, $format, $width, $height, ['align' => Alignment::Center]);

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
