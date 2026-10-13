<?php

namespace Rikochey\Data;

use DateTimeImmutable;

/**
 * A website of the token's organization. Its signing key is never exposed by the API.
 */
final class Website
{
    /**
     * @param  list<Department>  $departments
     */
    public function __construct(
        public readonly string $id,
        public readonly string $name,
        public readonly string $host,
        public readonly string $url,
        public readonly ?string $email,
        public readonly ?string $company,
        public readonly ?string $address,
        public readonly ?string $zipcode,
        public readonly ?string $city,
        public readonly ?string $country,
        public readonly ?string $phone,
        public readonly ?string $logo,
        public readonly array $departments,
        public readonly ?DateTimeImmutable $createdAt,
        public readonly ?DateTimeImmutable $updatedAt,
    ) {}

    /**
     * Whether a page served on this host may submit the website's forms: the API only accepts
     * submissions whose origin matches the website's domain.
     */
    public function servesHost(string $host): bool
    {
        return self::normalizeHost($host) === self::normalizeHost($this->host);
    }

    /**
     * The equivalence the platform applies to domains: lower case, no scheme, path or port,
     * `www.` optional. `https://WWW.Example.com:443/contact` becomes `example.com`.
     */
    public static function normalizeHost(string $host): string
    {
        $host = strtolower(trim($host));
        $host = (string) preg_replace('#^[a-z][a-z0-9+.-]*://#', '', $host);
        $host = (string) preg_replace('#[/?\#].*$#', '', $host);
        $host = (string) preg_replace('#:\d+$#', '', $host);
        $host = rtrim($host, '.');

        return (string) preg_replace('/^www\./', '', $host);
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $string = static fn (string $key): ?string => isset($data[$key]) ? (string) $data[$key] : null;

        return new self(
            id: (string) $data['id'],
            name: (string) $data['name'],
            host: (string) $data['host'],
            url: (string) ($data['url'] ?? 'https://'.$data['host']),
            email: $string('email'),
            company: $string('company'),
            address: $string('address'),
            zipcode: $string('zipcode'),
            city: $string('city'),
            country: $string('country'),
            phone: $string('phone'),
            logo: $string('logo'),
            departments: array_map(Department::fromArray(...), array_values((array) ($data['departments'] ?? []))),
            createdAt: Dates::parse($data['created_at'] ?? null),
            updatedAt: Dates::parse($data['updated_at'] ?? null),
        );
    }
}
