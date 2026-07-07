<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Exceptions;

/** The targeted printer is offline/unreachable — thrown from resource methods that check status before acting. */
class PrinterOfflineException extends PrintAgentException
{
    public function __construct(public readonly string $printerId, string $message = '')
    {
        parent::__construct($message !== '' ? $message : "Printer \"{$printerId}\" is offline");
    }
}
