<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `QrErrorCorrectionLevel` in the Print Agent (src/document/elements/qrcode.element.ts). */
enum QrErrorCorrection: string
{
    case Low = 'L';
    case Medium = 'M';
    case Quartile = 'Q';
    case High = 'H';
}
