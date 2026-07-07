<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `CharacterSize` in the Print Agent (src/document/interfaces/style.types.ts). */
enum CharacterSize: string
{
    case Normal = 'normal';
    case DoubleWidth = 'double-width';
    case DoubleHeight = 'double-height';
    case DoubleWidthHeight = 'double-width-height';
}
