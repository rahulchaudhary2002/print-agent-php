<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Exceptions;

/** The Print Agent could not be reached at all (connection refused, DNS failure, agent not running). */
class ConnectionException extends PrintAgentException {}
