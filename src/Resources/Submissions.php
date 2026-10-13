<?php

namespace Rikochey\Resources;

use Rikochey\Client;
use Rikochey\Data\Captcha;
use Rikochey\Data\Submission;

/**
 * Requests sent from a server, on behalf of a website of the token's organization.
 *
 * The token replaces the website signature, not the captcha: when the website's chain has
 * links, a token from one of them is required.
 */
final class Submissions
{
    public function __construct(private readonly Client $client) {}

    /**
     * `POST /api/websites/{website}/forms/{slug}/submit`
     *
     * The chain link key and its token travel as headers; the fallback grant travels in the body,
     * in the `captcha_fallback` field, which the API strips before recording the request.
     *
     * @param  array<string, mixed>  $fields  Form fields by field name, plus `page_url`, `page_title` or `visitor_hash` when known.
     */
    public function submit(string $websiteId, string $formSlug, array $fields, ?Captcha $captcha = null): Submission
    {
        $headers = $captcha === null ? [] : [
            'X-Captcha-Provider' => $captcha->provider,
            'X-Captcha-Token' => $captcha->token,
        ];

        if ($captcha?->fallbackToken !== null) {
            $fields['captcha_fallback'] = $captcha->fallbackToken;
        }

        return Submission::fromArray($this->client->request(
            'POST',
            '/api/websites/'.rawurlencode($websiteId).'/forms/'.rawurlencode($formSlug).'/submit',
            $fields,
            $headers,
        ));
    }
}
