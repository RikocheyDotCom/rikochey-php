<?php

namespace Rikochey\Data;

use DateTimeImmutable;
use Exception;

/**
 * @internal
 */
final class Dates
{
    /**
     * The API sends ISO 8601 dates, in UTC.
     */
    public static function parse(mixed $value): ?DateTimeImmutable
    {
        if (! is_string($value) || $value === '') {
            return null;
        }

        try {
            return new DateTimeImmutable($value);
        } catch (Exception) {
            return null;
        }
    }
}
