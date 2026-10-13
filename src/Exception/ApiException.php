<?php

namespace Rikochey\Exception;

use Rikochey\Client;
use Rikochey\Http\Response;

/**
 * The API answered with an error. The subclasses name the statuses an integration usually
 * handles; any other status arrives here.
 */
class ApiException extends RikocheyException
{
    /**
     * @param  array<string, mixed>  $payload  Decoded JSON body, empty when there is none.
     */
    final public function __construct(
        string $message,
        public readonly int $status,
        public readonly ?string $errorCode = null,
        public readonly array $payload = [],
    ) {
        parent::__construct($message, $status);
    }

    public static function fromResponse(Response $response): static
    {
        $payload = json_decode($response->body, true);
        $payload = is_array($payload) ? $payload : [];
        $errorCode = isset($payload['error']) && is_string($payload['error']) ? $payload['error'] : null;
        $message = isset($payload['message']) && is_string($payload['message'])
            ? $payload['message']
            : ($errorCode ?? Client::PRODUCT.' returned HTTP '.$response->status.'.');

        return new static($message, $response->status, $errorCode, $payload);
    }
}
