<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Exceptions;

use RuntimeException;

/** Base type for every exception this SDK throws — catch this to handle any Print Agent SDK failure. */
class PrintAgentException extends RuntimeException {}
