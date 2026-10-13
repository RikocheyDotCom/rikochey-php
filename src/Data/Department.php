<?php

namespace Rikochey\Data;

final class Department
{
    public function __construct(
        public readonly string $code,
        public readonly string $name,
        public readonly ?string $region,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self((string) $data['code'], (string) $data['name'], isset($data['region']) ? (string) $data['region'] : null);
    }
}
