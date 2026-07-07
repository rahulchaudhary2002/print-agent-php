<?php

declare(strict_types=1);

namespace PrintAgent\Sdk\Support;

use Illuminate\Http\Client\Response;
use PrintAgent\Sdk\Exceptions\ApiException;

/**
 * Understands exactly one thing: the Print Agent's response envelope
 * (`{success, message, data}` / `{success, message, errors}`, see
 * src/api/responses/response-envelope.ts in the agent). Nothing else in this SDK should parse a
 * raw HTTP response directly.
 */
final class ResponseParser
{
    /** @return array<string, mixed> */
    public static function data(Response $response): array
    {
        /** @var array<string, mixed> $body */
        $body = $response->json() ?? [];

        if (($body['success'] ?? false) !== true) {
            /** @var array<int, string> $errors */
            $errors = array_map('strval', (array) ($body['errors'] ?? []));
            $message = (string) ($body['message'] ?? 'The Print Agent rejected the request');

            throw new ApiException($message, $errors, $response->status());
        }

        /** @var array<string, mixed>|mixed $data */
        $data = $body['data'] ?? [];

        return is_array($data) ? $data : ['value' => $data];
    }
}
