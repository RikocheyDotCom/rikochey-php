<?php

namespace Rikochey\Http;

use Rikochey\Client;
use Rikochey\Exception\TransportException;

/**
 * Default transport when the curl extension is loaded.
 */
final class CurlTransport implements Transport
{
    public function __construct(
        private readonly int $timeoutSeconds = 10,
        private readonly int $connectTimeoutSeconds = 5,
    ) {}

    public function send(Request $request): Response
    {
        $headers = [];
        $headerLines = [];
        $handle = curl_init($request->url);

        foreach ($request->headers as $name => $value) {
            $headerLines[] = $name.': '.$value;
        }

        curl_setopt_array($handle, [
            CURLOPT_CUSTOMREQUEST => $request->method,
            CURLOPT_HTTPHEADER => $headerLines,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_FOLLOWLOCATION => false,
            CURLOPT_TIMEOUT => $this->timeoutSeconds,
            CURLOPT_CONNECTTIMEOUT => $this->connectTimeoutSeconds,
            CURLOPT_HEADERFUNCTION => static function ($handle, string $line) use (&$headers): int {
                $parts = explode(':', $line, 2);

                if (count($parts) === 2) {
                    $headers[strtolower(trim($parts[0]))] = trim($parts[1]);
                }

                return strlen($line);
            },
        ]);

        if ($request->body !== null) {
            curl_setopt($handle, CURLOPT_POSTFIELDS, $request->body);
        }

        $body = curl_exec($handle);

        if (! is_string($body)) {
            throw new TransportException(Client::PRODUCT.' could not be reached: '.curl_error($handle));
        }

        return new Response((int) curl_getinfo($handle, CURLINFO_RESPONSE_CODE), $headers, $body);
    }
}
