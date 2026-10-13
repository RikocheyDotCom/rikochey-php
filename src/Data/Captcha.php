<?php

namespace Rikochey\Data;

use InvalidArgumentException;
use Rikochey\Exception\AuthorizationException;

/**
 * A captcha token, sent with a submission when the website's chain requires one.
 */
final class Captcha
{
    public function __construct(
        /** Key of the chain link that produced the token — `recaptcha_v3:12`, `altcha:7`. */
        public readonly string $provider,
        public readonly string $token,
        /**
         * The `fallback_token` the API returned with a `captcha_fallback` error, when this token
         * was solved at the link that error designated. Valid for two minutes, and for that link
         * only: without it, a third-party link the API did not designate is refused with
         * `captcha_provider_not_allowed`.
         */
        public readonly ?string $fallbackToken = null,
    ) {}

    /**
     * The captcha to send when replaying a submission after a `captcha_fallback`: the link the
     * API designated, the token solved at that link, and the grant that authorizes it.
     */
    public static function afterFallback(AuthorizationException $exception, string $token): self
    {
        $provider = $exception->nextCaptchaProvider();

        if ($provider === null) {
            throw new InvalidArgumentException('This error does not designate a next chain link: there is no fallback to follow.');
        }

        return new self($provider, $token, $exception->captchaFallbackToken());
    }
}
