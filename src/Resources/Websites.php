<?php

namespace Rikochey\Resources;

use Rikochey\Client;
use Rikochey\Data\EventActions;
use Rikochey\Data\Website;
use Rikochey\Data\WebsiteForms;
use Rikochey\Exception\ConflictException;
use Rikochey\Exception\ValidationException;

/**
 * Websites of the token's organization.
 */
final class Websites
{
    public function __construct(private readonly Client $client) {}

    /**
     * `GET /api/websites`, sorted by name.
     *
     * @return list<Website>
     */
    public function all(): array
    {
        return $this->list($this->client->request('GET', '/api/websites'));
    }

    /**
     * `GET /api/websites/{website}`
     */
    public function get(string $websiteId): Website
    {
        return Website::fromArray((array) $this->client->request('GET', '/api/websites/'.rawurlencode($websiteId))['data']);
    }

    /**
     * `GET /api/websites/by-department/{code}`. The API answers 404 when no website serves the
     * department: an empty list is returned instead.
     *
     * @return list<Website>
     */
    public function inDepartment(string $code): array
    {
        return $this->list($this->client->request('GET', '/api/websites/by-department/'.rawurlencode($code), allowNotFound: true));
    }

    /**
     * `GET /api/websites/{website}/forms`: the script address and the active forms.
     */
    public function forms(string $websiteId): WebsiteForms
    {
        return WebsiteForms::fromArray($this->client->request('GET', '/api/websites/'.rawurlencode($websiteId).'/forms'));
    }

    /**
     * `GET /api/websites/{website}/event-actions`: the JavaScript actions of the website. Needs a
     * token with the `website:actions` permission.
     */
    public function eventActions(string $websiteId): EventActions
    {
        return EventActions::fromArray((array) $this->client->request('GET', $this->eventActionsPath($websiteId))['data']);
    }

    /**
     * `PUT /api/websites/{website}/event-actions`: replaces all the JavaScript actions of the
     * website; an event left out or empty loses its action. `$version` is the one read with
     * `eventActions()`.
     *
     * @param  array<string, ?string>  $actions  code by event
     *
     * @throws ValidationException code refused, messages under `actions.{event}`
     * @throws ConflictException the actions were changed elsewhere since they were read
     */
    public function updateEventActions(string $websiteId, array $actions, string $version): EventActions
    {
        $body = $this->client->request('PUT', $this->eventActionsPath($websiteId), [
            'actions' => (object) $actions,
            'version' => $version,
        ]);

        return EventActions::fromArray((array) $body['data']);
    }

    private function eventActionsPath(string $websiteId): string
    {
        return '/api/websites/'.rawurlencode($websiteId).'/event-actions';
    }

    /**
     * @param  array<string, mixed>  $body
     * @return list<Website>
     */
    private function list(array $body): array
    {
        return array_map(Website::fromArray(...), array_values((array) ($body['data'] ?? [])));
    }
}
