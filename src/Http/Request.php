<?php

namespace Rikochey\Http;

/**
 * An HTTP request, ready to be sent by a transport.
 */
final class Request
{
    /**
     * @param  array<string, string>  $headers
     */
    public function __construct(
        public readonly string $method,
        public readonly string $url,
        public readonly array $headers = [],
        public readonly ?string $body = null,
    ) {}
}
