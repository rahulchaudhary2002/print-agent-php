<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Exceptions;

/** The request to the Print Agent exceeded the configured timeout. */
class TimeoutException extends PrintAgentException {}
