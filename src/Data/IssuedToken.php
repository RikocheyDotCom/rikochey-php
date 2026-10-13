<?php

namespace Rikochey\Data;

/**
 * A key, as returned the one time it is shown: by the panel, or by a creation on `/api/tokens`.
 * Store it — the platform keeps only a hash of it, so it can never be read back.
 */
final class IssuedToken
{
    public function __construct(
        public readonly string $token,
        public readonly int $teamId,
        public readonly string $teamSlug,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self((string) $data['token'], (int) ($data['team']['id'] ?? 0), (string) ($data['team']['slug'] ?? ''));
    }
}
