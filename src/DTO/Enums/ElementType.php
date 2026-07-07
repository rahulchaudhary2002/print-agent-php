<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `ElementType` in the Print Agent (src/document/interfaces/element-type.enum.ts). */
enum ElementType: string
{
    case Text = 'text';
    case Divider = 'divider';
    case Table = 'table';
    case Image = 'image';
    case QrCode = 'qrcode';
    case Barcode = 'barcode';
    case Feed = 'feed';
    case Cut = 'cut';
    case Drawer = 'drawer';
}
