<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `ImageFormat` in the Print Agent (src/document/elements/image.element.ts). */
enum ImageFormat: string
{
    case Png = 'png';
    case Jpeg = 'jpeg';
    case Bmp = 'bmp';
}
