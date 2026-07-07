<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Exceptions;

/** Thrown client-side, before a request is even sent, when a builder or resource call is given invalid input. */
class ValidationException extends PrintAgentException
{
    /** @param array<int, string> $errors */
    public function __construct(string $message, public readonly array $errors = [])
    {
        parent::__construct($message);
    }
}
