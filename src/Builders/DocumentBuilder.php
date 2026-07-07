<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Builders;

use PrintAgent\Sdk\DTO\Enums\Alignment;
use PrintAgent\Sdk\DTO\Enums\BarcodeType;
use PrintAgent\Sdk\DTO\Enums\CharacterSize;
use PrintAgent\Sdk\DTO\Enums\CutType;
use PrintAgent\Sdk\DTO\Enums\ElementType;
use PrintAgent\Sdk\DTO\Enums\FontFamily;
use PrintAgent\Sdk\DTO\Enums\ImageFormat;
use PrintAgent\Sdk\DTO\Enums\QrErrorCorrection;
use PrintAgent\Sdk\DTO\Enums\TextEncoding;
use PrintAgent\Sdk\Exceptions\ValidationException;

/**
 * Generic, fluent JSON document builder — a byte-for-byte-compatible producer of the exact
 * `PrintDocument` shape the Print Agent's own document engine consumes
 * (src/document/interfaces/document.types.ts). This class never generates ESC/POS bytes and
 * knows nothing about any specific printer driver; it only ever produces plain arrays that
 * `json_encode` into what the agent expects as a job's `payload`.
 *
 * @example
 * $document = DocumentBuilder::make()
 *     ->center('Restaurant')
 *     ->line()
 *     ->text('Invoice #1001')
 *     ->feed()
 *     ->cut();
 */
final class DocumentBuilder
{
    /** @var array<int, array<string, mixed>> */
    private array $sections = [];

    /** @var array<int, array<string, mixed>> */
    private array $currentElements = [];

    /** @var array<string, mixed>|null */
    private ?array $currentSectionStyle = null;

    /** @var array<string, mixed>|null */
    private ?array $documentStyle = null;

    /** @var array<string, mixed>|null */
    private ?array $metadata = null;

    public static function make(): self
    {
        return new self;
    }

    /**
     * @param  array<string, mixed>  $style  align, font, bold, underline, inverse, characterSize, margins, lineHeight, letterSpacing
     */
    public function text(string $content, array $style = [], TextEncoding $encoding = TextEncoding::Utf8): static
    {
        $this->currentElements[] = array_filter([
            'type' => ElementType::Text->value,
            'content' => $content,
            'encoding' => $encoding->value,
            'style' => self::normalizeStyle($style),
        ], static fn (mixed $value): bool => $value !== null);

        return $this;
    }

    /** @param array<string, mixed> $style */
    public function center(string $content, array $style = []): static
    {
        return $this->text($content, ['align' => Alignment::Center, ...$style]);
    }

    /** @param array<string, mixed> $style */
    public function left(string $content, array $style = []): static
    {
        return $this->text($content, ['align' => Alignment::Left, ...$style]);
    }

    /** @param array<string, mixed> $style */
    public function right(string $content, array $style = []): static
    {
        return $this->text($content, ['align' => Alignment::Right, ...$style]);
    }

    /** @param array<string, mixed> $style */
    public function bold(string $content, array $style = []): static
    {
        return $this->text($content, ['bold' => true, ...$style]);
    }

    /** @param array<string, mixed> $style */
    public function large(string $content, array $style = []): static
    {
        return $this->text($content, ['characterSize' => CharacterSize::DoubleWidthHeight, ...$style]);
    }

    public function line(string $character = '-', int $length = 32): static
    {
        if (mb_strlen($character) !== 1) {
            throw new ValidationException('Divider character must be exactly one character');
        }

        $this->currentElements[] = [
            'type' => ElementType::Divider->value,
            'character' => $character,
            'length' => $length,
            'style' => (object) [],
        ];

        return $this;
    }

    /**
     * @param  array<int, array{header?: string, width: int, align?: Alignment, padding?: int, wrap?: bool, truncate?: bool}>  $columns
     * @param  array<int, array<int, string>>  $rows  each a plain list of cell strings, one per column
     * @param  array<int, array<string, mixed>>  $rowStyles  style overrides keyed by row index — e.g. `[2 => ['bold' => true]]` to bold just row 2
     */
    public function table(array $columns, array $rows, array $rowStyles = []): static
    {
        if ($columns === []) {
            throw new ValidationException('A table needs at least one column');
        }

        $widthSum = array_sum(array_map(static fn (array $column): int => $column['width'] + ($column['padding'] ?? 0), $columns));
        if ($widthSum > 64) {
            throw new ValidationException("Table column widths (plus padding) sum to {$widthSum}, which exceeds the 64-character maximum");
        }

        $normalizedRows = [];
        foreach ($rows as $index => $cells) {
            if (count($cells) !== count($columns)) {
                throw new ValidationException("Row {$index} has ".count($cells)." cells, expected ".count($columns));
            }

            $normalizedRow = ['cells' => array_values($cells)];
            if (isset($rowStyles[$index]) && $rowStyles[$index] !== []) {
                $normalizedRow['style'] = self::normalizeStyle($rowStyles[$index]);
            }
            $normalizedRows[] = $normalizedRow;
        }

        $this->currentElements[] = [
            'type' => ElementType::Table->value,
            'columns' => array_map(static function (array $column): array {
                $normalized = $column;
                if (isset($normalized['align']) && $normalized['align'] instanceof Alignment) {
                    $normalized['align'] = $normalized['align']->value;
                }

                return $normalized;
            }, $columns),
            'rows' => $normalizedRows,
            'style' => (object) [],
        ];

        return $this;
    }

    /** @param array<string, mixed> $style */
    public function qrCode(
        string $content,
        int $size = 4,
        QrErrorCorrection $errorCorrection = QrErrorCorrection::Medium,
        int $margin = 0,
        int $model = 2,
        array $style = [],
    ): static {
        if ($size < 1 || $size > 16) {
            throw new ValidationException('QR code size must be between 1 and 16');
        }
        if (! in_array($model, [1, 2], true)) {
            throw new ValidationException('QR code model must be 1 or 2');
        }

        $this->currentElements[] = [
            'type' => ElementType::QrCode->value,
            'content' => $content,
            'size' => $size,
            'errorCorrection' => $errorCorrection->value,
            'margin' => $margin,
            'model' => $model,
            'style' => self::normalizeStyle($style),
        ];

        return $this;
    }

    /** @param array<string, mixed> $style */
    public function barcode(
        BarcodeType $type,
        string $value,
        ?int $height = null,
        ?int $width = null,
        bool $showText = true,
        array $style = [],
    ): static {
        if (! preg_match($type->pattern(), $value)) {
            throw new ValidationException("Value \"{$value}\" is not valid for barcode type {$type->value}");
        }

        $this->currentElements[] = array_filter([
            'type' => ElementType::Barcode->value,
            'barcodeType' => $type->value,
            'value' => $value,
            'height' => $height,
            'width' => $width,
            'showText' => $showText,
            'style' => self::normalizeStyle($style),
        ], static fn (mixed $v): bool => $v !== null);

        return $this;
    }

    /** @param array<string, mixed> $style */
    public function image(string $path, ?ImageFormat $format = null, ?int $width = null, ?int $height = null, array $style = []): static
    {
        if (trim($path) === '') {
            throw new ValidationException('Image path must not be empty');
        }

        $this->currentElements[] = array_filter([
            'type' => ElementType::Image->value,
            'source' => ['kind' => 'path', 'path' => $path],
            'format' => $format?->value,
            'width' => $width,
            'height' => $height,
            'style' => self::normalizeStyle($style),
        ], static fn (mixed $v): bool => $v !== null);

        return $this;
    }

    public function feed(int $lines = 1): static
    {
        $this->currentElements[] = [
            'type' => ElementType::Feed->value,
            'lines' => $lines,
        ];

        return $this;
    }

    public function cut(CutType $type = CutType::Full): static
    {
        $this->currentElements[] = [
            'type' => ElementType::Cut->value,
            'cutType' => $type->value,
        ];

        return $this;
    }

    public function drawer(int $pin = 0, int $durationMs = 120): static
    {
        if (! in_array($pin, [0, 1], true)) {
            throw new ValidationException('Cash drawer pin must be 0 or 1');
        }
        if ($durationMs < 1 || $durationMs > 510) {
            throw new ValidationException('Cash drawer duration must be between 1 and 510 ms');
        }

        $this->currentElements[] = [
            'type' => ElementType::Drawer->value,
            'pin' => $pin,
            'durationMs' => $durationMs,
        ];

        return $this;
    }

    /** Starts a new section, e.g. to give the receipt body a different default style than its header. */
    public function newSection(): static
    {
        $this->flushSection();

        return $this;
    }

    /** @param array<string, mixed> $metadata */
    public function metadata(array $metadata): static
    {
        $this->metadata = $metadata;

        return $this;
    }

    /** @return array<string, mixed> */
    public function toArray(): array
    {
        $this->flushSection();

        if ($this->sections === []) {
            throw new ValidationException('A document must contain at least one element before it can be built');
        }

        $document = ['sections' => $this->sections];
        if ($this->documentStyle !== null) {
            $document['style'] = $this->documentStyle;
        }
        if ($this->metadata !== null) {
            $document['metadata'] = $this->metadata;
        }

        return $document;
    }

    public function toJson(): string
    {
        $json = json_encode($this->toArray(), JSON_THROW_ON_ERROR);

        return $json;
    }

    private function flushSection(): void
    {
        if ($this->currentElements === []) {
            return;
        }

        $section = ['elements' => $this->currentElements];
        if ($this->currentSectionStyle !== null) {
            $section['style'] = $this->currentSectionStyle;
        }

        $this->sections[] = $section;
        $this->currentElements = [];
        $this->currentSectionStyle = null;
    }

    /**
     * Converts enum values in a style array to their raw string values and drops null entries —
     * mirrors the agent's `ElementStyle`, where every field is optional and simply absent (never
     * `null`) when not set. Returns a `stdClass`, never a plain empty array: PHP's `json_encode`
     * renders an empty array as JSON `[]`, not `{}`, which would silently corrupt the document
     * shape the agent expects for every element with no explicit style (the common case).
     *
     * @param  array<string, mixed>  $style
     * @return array<string, mixed>|\stdClass
     */
    private static function normalizeStyle(array $style): \stdClass|array
    {
        $normalized = [];
        foreach ($style as $key => $value) {
            if ($value === null) {
                continue;
            }
            $normalized[$key] = $value instanceof \BackedEnum ? $value->value : $value;
        }

        return $normalized === [] ? (object) [] : $normalized;
    }
}
