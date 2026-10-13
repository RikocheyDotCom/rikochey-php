<?php

namespace Rikochey\Http;

use Rikochey\Exception\TransportException;

/**
 * Sends requests to the API. Implement it to use your framework's HTTP client — a WordPress
 * plugin, for instance, sends them through `wp_remote_request()`.
 */
interface Transport
{
    /**
     * Returns the response whatever its status: HTTP errors are handled by the client.
     *
     * @throws TransportException When no response could be obtained.
     */
    public function send(Request $request): Response;
}
