<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Exceptions;

use Throwable;

/** The Print Agent responded with `{"success": false, ...}` — the request reached the agent, but it rejected it. */
class ApiException extends PrintAgentException
{
    /** @param array<int, string> $errors */
    public function __construct(
        string $message,
        public readonly array $errors = [],
        public readonly int $statusCode = 0,
        ?Throwable $previous = null,
    ) {
        parent::__construct($message, $statusCode, $previous);
    }
}
