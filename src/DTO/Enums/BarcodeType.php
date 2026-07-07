<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `BarcodeType` in the Print Agent (src/document/elements/barcode.element.ts). */
enum BarcodeType: string
{
    case Ean13 = 'EAN13';
    case Ean8 = 'EAN8';
    case Upc = 'UPC';
    case Code39 = 'CODE39';
    case Code128 = 'CODE128';
    case Itf = 'ITF';
    case Codabar = 'CODABAR';

    /**
     * Mirrors the builder-side validation regexes on the Print Agent — the server does not
     * re-validate these, so an SDK that skips this check can silently produce bad barcodes.
     */
    public function pattern(): string
    {
        return match ($this) {
            self::Ean13 => '/^\d{12,13}$/',
            self::Ean8 => '/^\d{7,8}$/',
            self::Upc => '/^\d{11,12}$/',
            self::Code39 => '/^[0-9A-Z\-. $\/+%]+$/',
            self::Code128 => '/^[\x20-\x7E]+$/',
            self::Itf => '/^(\d{2})+$/',
            self::Codabar => '/^[A-D]?[0-9\-$:\/.+]+[A-D]?$/',
        };
    }
}
