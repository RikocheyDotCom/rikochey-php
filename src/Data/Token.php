<?php

namespace Rikochey\Data;

use DateTimeImmutable;

/**
 * A token of the bearer's account. Its value is never listed: it is only shown at creation.
 */
final class Token
{
    /**
     * @param  list<string>  $abilities
     */
    public function __construct(
        public readonly int $id,
        public readonly string $name,
        public readonly ?int $teamId,
        public readonly array $abilities,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $lastUsedAt,
        public readonly ?DateTimeImmutable $expiresAt,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            id: (int) $data['id'],
            name: (string) $data['name'],
            teamId: isset($data['team_id']) ? (int) $data['team_id'] : null,
            abilities: array_values(array_map('strval', (array) ($data['abilities'] ?? []))),
            createdAt: Dates::parse($data['created_at'] ?? null),
            lastUsedAt: Dates::parse($data['last_used_at'] ?? null),
            expiresAt: Dates::parse($data['expires_at'] ?? null),
        );
    }
}
