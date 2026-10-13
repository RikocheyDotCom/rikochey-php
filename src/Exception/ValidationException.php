<?php

namespace Rikochey\Exception;

/**
 * 422: the request was understood, but some fields are invalid.
 */
class ValidationException extends ApiException
{
    /**
     * Messages by field name.
     *
     * @return array<string, list<string>>
     */
    public function errors(): array
    {
        $errors = $this->payload['errors'] ?? [];

        return is_array($errors) ? $errors : [];
    }
}
