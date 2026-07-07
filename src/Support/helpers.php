<?php

declare(strict_types=1);

use PrintAgent\Sdk\Contracts\PrintAgentClientContract;

if (! function_exists('print_agent')) {
    /**
     * Resolves the Print Agent client out of the container — the functional-style alternative
     * to the `PrintAgent` facade or constructor-injecting `PrintAgentClientContract`.
     *
     * @example print_agent()->printers()->list();
     */
    function print_agent(): PrintAgentClientContract
    {
        return app(PrintAgentClientContract::class);
    }
}
