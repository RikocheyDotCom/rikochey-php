<?php

namespace Rikochey\Exception;

use Rikochey\Http\Response;

/**
 * 429: too many requests. Wait `$retryAfter` seconds before trying again.
 */
class RateLimitException extends ApiException
{
    public ?int $retryAfter = null;

    public static function fromRateLimitedResponse(Response $response): self
    {
        $exception = self::fromResponse($response);
        $retryAfter = $response->header('Retry-After');
        $exception->retryAfter = $retryAfter !== null && ctype_digit($retryAfter) ? (int) $retryAfter : null;

        return $exception;
    }
}
