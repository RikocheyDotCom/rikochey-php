<?php

namespace Rikochey\Exception;

/**
 * 403: token from another organization, website disabled, origin, signature or captcha.
 * `$errorCode` carries the stable code when the API sends one — `captcha_required`,
 * `captcha_fallback`…
 */
class AuthorizationException extends ApiException
{
    /**
     * Chain link to resume with, on a `captcha_fallback` error.
     */
    public function nextCaptchaProvider(): ?string
    {
        return isset($this->payload['next']) && is_string($this->payload['next']) ? $this->payload['next'] : null;
    }

    /**
     * Grant authorizing that link, on a `captcha_fallback` error: send it back in the
     * `captcha_fallback` field of the replayed submission. Valid two minutes.
     */
    public function captchaFallbackToken(): ?string
    {
        return isset($this->payload['fallback_token']) && is_string($this->payload['fallback_token'])
            ? $this->payload['fallback_token']
            : null;
    }
}
