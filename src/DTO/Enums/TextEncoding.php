<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `TextEncoding` in the Print Agent (src/document/elements/text.element.ts). */
enum TextEncoding: string
{
    case Utf8 = 'utf-8';
    case Ascii = 'ascii';
}
