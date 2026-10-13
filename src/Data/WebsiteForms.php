<?php

namespace Rikochey\Data;

use ArrayIterator;
use Countable;
use IteratorAggregate;
use Traversable;

/**
 * What a CMS needs to place forms on a website: the script and the forms.
 *
 * @implements IteratorAggregate<int, Form>
 */
final class WebsiteForms implements Countable, IteratorAggregate
{
    /**
     * @param  list<Form>  $forms
     */
    public function __construct(
        /** To load on every page of the website. */
        public readonly string $scriptUrl,
        public readonly array $forms,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        return new self(
            scriptUrl: (string) ($data['script_url'] ?? ''),
            forms: array_map(Form::fromArray(...), array_values((array) ($data['data'] ?? []))),
        );
    }

    public function find(string $slug): ?Form
    {
        foreach ($this->forms as $form) {
            if ($form->slug === $slug) {
                return $form;
            }
        }

        return null;
    }

    /**
     * The tag to paste just before `</body>`.
     */
    public function scriptTag(): string
    {
        return '<script src="'.htmlspecialchars($this->scriptUrl, ENT_QUOTES, 'UTF-8').'" defer></script>';
    }

    public function count(): int
    {
        return count($this->forms);
    }

    public function getIterator(): Traversable
    {
        return new ArrayIterator($this->forms);
    }
}
