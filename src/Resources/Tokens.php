<?php

namespace Rikochey\Resources;

use Rikochey\Client;
use Rikochey\Data\IssuedToken;
use Rikochey\Data\Token;

/**
 * Tokens of the bearer's account, across its organizations.
 */
final class Tokens
{
    public function __construct(private readonly Client $client) {}

    /**
     * `GET /api/tokens`
     *
     * @return list<Token>
     */
    public function all(): array
    {
        return array_map(Token::fromArray(...), array_values((array) ($this->client->request('GET', '/api/tokens')['data'] ?? [])));
    }

    /**
     * `POST /api/tokens`, for the account's default organization unless `$teamId` is given.
     */
    public function create(string $name, ?int $teamId = null): IssuedToken
    {
        return IssuedToken::fromArray($this->client->request('POST', '/api/tokens', array_filter(
            ['name' => $name, 'team_id' => $teamId],
            static fn (mixed $value): bool => $value !== null,
        )));
    }

    /**
     * `DELETE /api/tokens/{id}`, effective immediately.
     */
    public function revoke(int $tokenId): void
    {
        $this->client->request('DELETE', '/api/tokens/'.$tokenId);
    }

    /**
     * `DELETE /api/tokens`: every token of the account, **including the one used by this client**.
     */
    public function revokeAll(): void
    {
        $this->client->request('DELETE', '/api/tokens');
    }
}
