<?php

namespace Rikochey;

use InvalidArgumentException;
use Rikochey\Exception\ApiException;
use Rikochey\Exception\AuthenticationException;
use Rikochey\Exception\AuthorizationException;
use Rikochey\Exception\ConflictException;
use Rikochey\Exception\NotFoundException;
use Rikochey\Exception\RateLimitException;
use Rikochey\Exception\RikocheyException;
use Rikochey\Exception\ValidationException;
use Rikochey\Http\CurlTransport;
use Rikochey\Http\Request;
use Rikochey\Http\Response;
use Rikochey\Http\StreamTransport;
use Rikochey\Http\Transport;
use Rikochey\Resources\Submissions;
use Rikochey\Resources\Tokens;
use Rikochey\Resources\Websites;

/**
 * Client of the platform API, authenticated by an organization's API key.
 *
 *     $client = new Client('42|pQ7d…', 'https://api.example.com');
 *
 *     foreach ($client->websites()->all() as $website) {
 *         echo $website->name;
 *     }
 *
 * **The API knows no passwords.** A key is created in the panel, under API tokens, and shown
 * once: copy it into your configuration. It opens that organization's API and nothing else, and
 * it is revoked in one click without touching the account — which is exactly why no password ever
 * needs to reach your server.
 *
 * Every call is made from a server: never expose the key in a page.
 */
final class Client
{
    public const VERSION = '1.2.0';

    /**
     * Name of the platform, its identifier and the prefix of its embed script. Rewritten in the
     * archive served by the platform: the client always speaks the brand it was downloaded from.
     */
    public const PRODUCT = 'Rikochey';

    public const SLUG = 'rikochey';

    public const SCRIPT_PREFIX = 'rikochey';

    private readonly string $baseUrl;

    private readonly Transport $transport;

    private readonly string $token;

    public function __construct(
        #[\SensitiveParameter] string $token,
        string $baseUrl,
        ?Transport $transport = null,
        private readonly string $userAgent = self::SLUG.'-php/'.self::VERSION,
    ) {
        if (! self::isWellFormedToken($token)) {
            throw new InvalidArgumentException('A '.self::PRODUCT.' API token looks like "42|pQ7d…": copy it whole. Create one in the panel, under API tokens.');
        }

        $this->token = $token;
        $this->baseUrl = self::normalizeBaseUrl($baseUrl);
        $this->transport = $transport ?? self::defaultTransport();
    }

    /**
     * API tokens are Sanctum tokens: `id|secret`.
     */
    public static function isWellFormedToken(string $token): bool
    {
        return preg_match('/^\d+\|[A-Za-z0-9_-]{20,}$/', $token) === 1;
    }

    /**
     * Keeps the key out of a `var_dump()`, a `print_r()` or a crash report: a client object ends
     * up in a log far more often than anyone intends.
     *
     * @return array<string, mixed>
     */
    public function __debugInfo(): array
    {
        return [
            'baseUrl' => $this->baseUrl,
            'token' => '***',
            'userAgent' => $this->userAgent,
        ];
    }

    public function websites(): Websites
    {
        return new Websites($this);
    }

    public function submissions(): Submissions
    {
        return new Submissions($this);
    }

    public function tokens(): Tokens
    {
        return new Tokens($this);
    }

    /**
     * Sends an authenticated request and returns the decoded body.
     *
     * @internal Used by the resources.
     *
     * @param  array<string, mixed>|null  $body
     * @param  array<string, string>  $headers
     * @return array<string, mixed>
     *
     * @throws RikocheyException
     */
    public function request(string $method, string $path, ?array $body = null, array $headers = [], bool $allowNotFound = false): array
    {
        $response = $this->send($method, $path, $body, $headers);

        if ($allowNotFound && $response->status === 404) {
            return [];
        }

        return self::decode($response);
    }

    /**
     * @param  array<string, mixed>|null  $body
     * @param  array<string, string>  $headers
     */
    private function send(string $method, string $path, ?array $body, array $headers): Response
    {
        return $this->transport->send(new Request(
            $method,
            $this->baseUrl.$path,
            [
                'Accept' => 'application/json',
                'Authorization' => 'Bearer '.$this->token,
                'User-Agent' => $this->userAgent,
                ...($body === null ? [] : ['Content-Type' => 'application/json']),
                ...$headers,
            ],
            $body === null ? null : json_encode($body, JSON_THROW_ON_ERROR | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES),
        ));
    }

    /**
     * @return array<string, mixed>
     */
    private static function decode(Response $response): array
    {
        if ($response->status >= 200 && $response->status < 300) {
            $body = json_decode($response->body, true);

            if (! is_array($body)) {
                throw new ApiException(self::PRODUCT.' returned a response that is not JSON.', $response->status);
            }

            return $body;
        }

        throw match ($response->status) {
            401 => AuthenticationException::fromResponse($response),
            403 => AuthorizationException::fromResponse($response),
            404 => NotFoundException::fromResponse($response),
            409 => ConflictException::fromResponse($response),
            422 => ValidationException::fromResponse($response),
            429 => RateLimitException::fromRateLimitedResponse($response),
            default => ApiException::fromResponse($response),
        };
    }

    private static function normalizeBaseUrl(string $baseUrl): string
    {
        $baseUrl = rtrim(trim($baseUrl), '/');

        if (! preg_match('#^https?://[^/]+#i', $baseUrl)) {
            throw new InvalidArgumentException('The base URL must start with https:// — for instance https://api.example.com');
        }

        return $baseUrl;
    }

    private static function defaultTransport(): Transport
    {
        return extension_loaded('curl') ? new CurlTransport : new StreamTransport;
    }
}
