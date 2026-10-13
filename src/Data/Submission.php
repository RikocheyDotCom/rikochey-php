<?php

namespace Rikochey\Data;

/**
 * A recorded request.
 */
final class Submission
{
    public function __construct(
        /** The confirmation message defined by the client: display it as is. */
        public readonly string $message,
        public readonly ?string $leadId,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self((string) ($data['message'] ?? ''), isset($data['lead_id']) ? (string) $data['lead_id'] : null);
    }
}
