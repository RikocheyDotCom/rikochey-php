<?php

namespace Rikochey\Http;

/**
 * An HTTP response, as returned by a transport.
 */
final class Response
{
    /**
     * @var array<string, string>
     */
    public readonly array $headers;

    /**
     * @param  array<string, string>  $headers  Header names in any case.
     */
    public function __construct(
        public readonly int $status,
        array $headers = [],
        public readonly string $body = '',
    ) {
        $this->headers = array_change_key_case($headers, CASE_LOWER);
    }

    public function header(string $name): ?string
    {
        return $this->headers[strtolower($name)] ?? null;
    }
}
