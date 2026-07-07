<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\DTO\Enums;

/** Mirrors `PrinterStatusValue` in the Print Agent (src/printer/interfaces/printer-status.enum.ts). */
enum PrinterStatus: string
{
    case Online = 'online';
    case Offline = 'offline';
    case Busy = 'busy';
    case Error = 'error';
    case Unknown = 'unknown';
}
