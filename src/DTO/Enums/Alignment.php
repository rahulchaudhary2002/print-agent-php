<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `TextAlignment` in the Print Agent (src/document/interfaces/style.types.ts). */
enum Alignment: string
{
    case Left = 'left';
    case Center = 'center';
    case Right = 'right';
}
