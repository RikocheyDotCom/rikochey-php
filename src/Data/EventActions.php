<?php

namespace Rikochey\Data;

/**
 * The JavaScript actions of a website: code run by its script at each event (`success`,
 * `form-ready`…), with the version to send back when saving them.
 */
final class EventActions
{
    /**
     * @param  array<string, ?string>  $actions  code by event, in the order of the API; null without action
     */
    public function __construct(
        public readonly array $actions,
        /** Send it back to `updateEventActions()`: a change made elsewhere since is then refused. */
        public readonly string $version,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function fromArray(array $data): self
    {
        $actions = [];

        foreach ((array) ($data['events'] ?? []) as $event) {
            if (is_array($event) && is_string($event['event'] ?? null)) {
                $actions[$event['event']] = is_string($event['code'] ?? null) ? $event['code'] : null;
            }
        }

        return new self($actions, (string) ($data['version'] ?? ''));
    }

    /**
     * Event names, in the order of the API.
     *
     * @return list<string>
     */
    public function events(): array
    {
        return array_keys($this->actions);
    }

    public function code(string $event): ?string
    {
        return $this->actions[$event] ?? null;
    }

    public function count(): int
    {
        return count(array_filter($this->actions, fn (?string $code): bool => $code !== null));
    }
}
