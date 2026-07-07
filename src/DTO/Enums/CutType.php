<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `CutType` in the Print Agent (src/document/commands/cut.command.ts). */
enum CutType: string
{
    case Full = 'full';
    case Partial = 'partial';
}
