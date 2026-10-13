<?php

namespace Rikochey\Data;

use Rikochey\Client;

/**
 * An active form linked to a website.
 */
final class Form
{
    /**
     * Source language of a form when the platform does not say.
     */
    public const DEFAULT_LOCALE = 'fr';

    /**
     * Languages the form offers, its source language first. The script renders the form in one of
     * them, the source language when the page asks for another.
     *
     * @var list<string>
     */
    public readonly array $locales;

    /**
     * @param  list<FormField>  $fields
     * @param  list<string>  $locales
     */
    public function __construct(
        /** The value of the embed script's form attribute, and the form of a submission. */
        public readonly string $slug,
        /** In the source language: the script translates it on the page. */
        public readonly string $name,
        /** When set, the script already places the form at this selector. */
        public readonly ?string $cssSelector,
        public readonly array $fields,
        /** The language the form is written in, in its editor. */
        public readonly string $locale = self::DEFAULT_LOCALE,
        array $locales = [],
    ) {
        $locales = array_filter($locales, static fn (mixed $code): bool => is_string($code) && $code !== '');

        $this->locales = array_values(array_unique([$locale, ...$locales]));
    }

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $locale = isset($data['locale']) && is_string($data['locale']) && $data['locale'] !== '' ? $data['locale'] : self::DEFAULT_LOCALE;

        return new self(
            slug: (string) $data['slug'],
            name: (string) $data['name'],
            cssSelector: isset($data['css_selector']) && $data['css_selector'] !== '' ? (string) $data['css_selector'] : null,
            fields: array_map(FormField::fromArray(...), array_values((array) ($data['fields'] ?? []))),
            locale: $locale,
            locales: array_values((array) ($data['locales'] ?? [])),
        );
    }

    /**
     * The tag that has the script render this form.
     *
     * @param  list<string>  $classes  CSS classes to put on the tag.
     * @param  string|null  $locale  Language to render the form in — see tag().
     */
    public function embedTag(array $classes = [], ?string $locale = null): string
    {
        return self::tag($this->slug, $classes, $locale);
    }

    /**
     * The same tag, from the form identifier alone — for a CMS that stores the identifier
     * without listing the forms on every page.
     *
     * Without a language, the script follows the `lang` attribute of the page's `<html>` tag.
     * A language the form does not offer renders it in its source language; a value that is not
     * a language code (`en`, `en-GB`, `en_GB`) is left out.
     *
     * @param  list<string>  $classes  CSS classes to put on the tag.
     * @param  string|null  $locale  Language to render the form in, whatever the page's language.
     */
    public static function tag(string $slug, array $classes = [], ?string $locale = null): string
    {
        $classes = array_values(array_filter($classes, static fn (string $class): bool => $class !== ''));
        $class = $classes === [] ? '' : ' class="'.htmlspecialchars(implode(' ', $classes), ENT_QUOTES, 'UTF-8').'"';

        $locale = $locale === null ? null : self::normalizeLocale($locale);
        $lang = $locale === null ? '' : ' data-'.Client::SCRIPT_PREFIX.'-lang="'.$locale.'"';

        return '<div'.$class.' data-'.Client::SCRIPT_PREFIX.'-form="'.htmlspecialchars($slug, ENT_QUOTES, 'UTF-8').'"'.$lang.'></div>';
    }

    /**
     * A language code as the script reads it — `en`, `en-GB` — or null when the value is not one.
     * The `en_GB` form of CMS locales is accepted.
     */
    public static function normalizeLocale(string $locale): ?string
    {
        $locale = str_replace('_', '-', trim($locale));

        return preg_match('/^(?=.{2,10}$)[A-Za-z]{2,8}(?:-[A-Za-z]{1,8})*$/', $locale) === 1 ? $locale : null;
    }
}
