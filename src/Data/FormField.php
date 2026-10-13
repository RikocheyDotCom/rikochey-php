<?php

namespace Rikochey\Data;

/**
 * A field of a form. Texts are in the form's source language: the script translates them on the page.
 */
final class FormField
{
    public function __construct(
        /** The `name` attribute expected in attached mode, and the key to submit. */
        public readonly string $name,
        public readonly string $label,
        public readonly string $type,
        public readonly bool $isRequired,
        /** The text shown in the empty field. */
        public readonly ?string $placeholder = null,
        /** The text shown under the field. */
        public readonly ?string $helpText = null,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $string = static fn (string $key): ?string => isset($data[$key]) && $data[$key] !== '' ? (string) $data[$key] : null;

        return new self(
            name: (string) $data['name'],
            label: (string) ($data['label'] ?? $data['name']),
            type: (string) ($data['type'] ?? 'text'),
            isRequired: (bool) ($data['is_required'] ?? false),
            placeholder: $string('placeholder'),
            helpText: $string('help_text'),
        );
    }
}
