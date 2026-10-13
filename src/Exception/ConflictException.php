<?php

namespace Rikochey\Exception;

/**
 * 409: the resource was changed since it was read. Read it again, then retry.
 */
class ConflictException extends ApiException
{
    /**
     * Current version of the resource, when the API returns it.
     */
    public function currentVersion(): ?string
    {
        $version = $this->payload['version'] ?? null;

        return is_string($version) ? $version : null;
    }
}
