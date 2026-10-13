# Rikochey PHP

Official PHP client for the Rikochey API: websites, forms, server-side submissions, JavaScript
actions and API tokens.

- **PHP 8.1+, no dependency**: curl when available, PHP streams otherwise
- **Key-only authentication**: the library never handles a password, and never shows the key in a dump
- **Typed, read-only responses**, and one exception per kind of error
- **Pluggable HTTP transport**: route requests through your framework's HTTP client, proxy and certificates included

The full guide is published with the API documentation:
[Client libraries → Rikochey PHP](https://www.rikochey.com/docs/api/libraries/php).

## Installation

```bash
composer require rikochey/rikochey-php
```

Without Composer, download a release, unzip it in your project and load its autoloader:

```php
require __DIR__.'/rikochey-php/autoload.php';
```

## Getting started

Create an API key in your workspace (**Organization → API tokens**), then:

```php
use Rikochey\Client;

$client = new Client('42|pQ7d…', 'https://www.rikochey.com');

foreach ($client->websites()->all() as $website) {
    $forms = $client->websites()->forms($website->id);

    echo $website->name.': '.count($forms).' forms'.PHP_EOL;
}
```

Keep the key on the server, in your configuration: it gives access to every website of your
organization, and must never reach a browser.

## What it covers

| Resource | Methods |
| --- | --- |
| Websites | `websites()->all()`, `get($id)`, `inDepartment($code)` |
| Forms of a website | `websites()->forms($id)`: the script tag and the active forms, with `Form::tag()` to place one in a page |
| Submissions | `submissions()->submit($websiteId, $formSlug, $fields, $captcha)` |
| JavaScript actions | `websites()->eventActions($id)`, `updateEventActions($id, $actions, $version)` |
| API tokens | `tokens()->all()`, `create($name)`, `revoke($id)`, `revokeAll()` |

### Place a form in a page

```php
$forms = $client->websites()->forms($website->id);

echo $forms->scriptTag();                    // <script src="…" defer></script>, on every page
echo $forms->find('contact')?->embedTag();   // <div data-rikochey-form="contact"></div>
```

### Submit a request from a server

```php
$submission = $client->submissions()->submit($website->id, 'contact', [
    'name' => 'Smith',
    'email' => 'client@example.com',
    'message' => 'Hello, I would like a quote.',
]);

echo $submission->message;  // the confirmation message of the form
```

## Errors

Every exception extends `Rikochey\Exception\RikocheyException`:

| Exception | When |
| --- | --- |
| `AuthenticationException` | `401`: key unknown, expired or revoked |
| `AuthorizationException` | `403`: another organization, website disabled, captcha (`errorCode`) |
| `NotFoundException` | `404`: unknown website, form or token |
| `ConflictException` | `409`: the JavaScript actions changed since they were read |
| `ValidationException` | `422`: invalid fields, `errors()` by field name |
| `RateLimitException` | `429`: wait `retryAfter` seconds |
| `ApiException` | any other status, or a response that is not JSON |
| `TransportException` | no response at all: DNS, TLS, timeout |

```php
use Rikochey\Exception\ValidationException;

try {
    $client->submissions()->submit($website->id, 'contact', $fields);
} catch (ValidationException $exception) {
    $exception->errors(); // ['email' => ['The email field must be a valid email address.']]
}
```

## Your own HTTP client

Implement `Rikochey\Http\Transport` (one `send(Request): Response` method) and pass it as the
third argument of the client. Return every HTTP response, error statuses included: the client
turns them into exceptions.

```php
$client = new Client($key, 'https://www.rikochey.com', new MyFrameworkTransport());
```

## License

MIT. See [LICENSE](LICENSE).
