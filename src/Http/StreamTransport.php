<?php

namespace Rikochey\Http;

use Rikochey\Client;
use Rikochey\Exception\TransportException;

/**
 * Fallback transport on PHP streams, for servers without the curl extension.
 */
final class StreamTransport implements Transport
{
    public function __construct(private readonly int $timeoutSeconds = 10) {}

    public function send(Request $request): Response
    {
        $headerLines = [];

        foreach ($request->headers as $name => $value) {
            $headerLines[] = $name.': '.$value;
        }

        $context = stream_context_create(['http' => [
            'method' => $request->method,
            'header' => implode("\r\n", $headerLines),
            'content' => $request->body ?? '',
            'timeout' => $this->timeoutSeconds,
            'ignore_errors' => true,
            'follow_location' => 0,
        ]]);

        $body = @file_get_contents($request->url, false, $context);

        // PHP 8.4 replaces the magic variable, deprecated from PHP 8.5.
        /** @var list<string> $responseHeaders */
        $responseHeaders = function_exists('http_get_last_response_headers')
            ? (http_get_last_response_headers() ?? [])
            : ($http_response_header ?? []);

        if ($body === false || $responseHeaders === []) {
            throw new TransportException(Client::PRODUCT.' could not be reached: '.(error_get_last()['message'] ?? 'no response'));
        }

        preg_match('#^HTTP/\S+\s+(\d{3})#', (string) array_shift($responseHeaders), $status);
        $headers = [];

        foreach ($responseHeaders as $line) {
            $parts = explode(':', $line, 2);

            if (count($parts) === 2) {
                $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
            }
        }

        return new Response((int) ($status[1] ?? 0), $headers, $body);
    }
}
