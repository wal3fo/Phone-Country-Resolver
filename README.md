# phone-country-resolver

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![Total Downloads](https://img.shields.io/packagist/dt/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![License](https://img.shields.io/packagist/l/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)

A lightweight Laravel package to resolve **ISO 3166-1 alpha-2 country codes** from phone numbers. Supports international prefixes, double-zero formats, numeric-only inputs, and local number fallbacks — with zero configuration required.

---

## Features

- Resolves country codes from international prefixes (e.g. `+1`, `0044`, `212`)
- Handles local phone formats starting with `0` via a configurable fallback
- Longest-prefix matching for accurate resolution of overlapping codes
- Registered as a singleton — supports both static calls and dependency injection
- Auto-discovered by Laravel — no manual provider registration needed

---

## Installation

```bash
composer require wal3fo/phone-country
```

### Local Development

To work with a local copy of the package, add a path repository to your project's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../Phone-Country-Resolver"
    }
]
```

Then require it normally:

```bash
composer require wal3fo/phone-country
```

---

## Usage

### Static Method

Call `resolveCountryCode` statically from anywhere in your application:

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

// Standard international format
PhoneCountryService::resolveCountryCode('+212612345678');       // → 'MA'

// Local number with explicit fallback
PhoneCountryService::resolveCountryCode('0612345678', 'MA');   // → 'MA'

// Unknown or unresolvable number (returns default)
PhoneCountryService::resolveCountryCode('0612345678');         // → 'XX'
```

### Dependency Injection

The service is registered as a singleton in Laravel's container and can be injected directly:

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

class UserProfileController extends Controller
{
    public function __construct(
        protected PhoneCountryService $phoneService
    ) {}

    public function update(Request $request)
    {
        $countryCode = $this->phoneService->resolveCountryCode(
            $request->phone_number
        );

        // ...
    }
}
```

---

## Examples

The service handles all common international and local phone number formats:

| Input | Result | Notes |
|-------|--------|-------|
| `+212612345678` | `MA` | Standard international format |
| `00212612345678` | `MA` | Double-zero prefix |
| `212612345678` | `MA` | Numeric-only international |
| `0612345678` | `XX` | Local format, no fallback provided |
| `0612345678` + `'MA'` | `MA` | Local format with fallback |
| `+1 242 123 4567` | `BS` | Formatted international (Bahamas) |

---

## Configuration

`resolveCountryCode` accepts an optional second parameter, `$localCountryCode`, used as a fallback when:

1. The number starts with `0` (local format).
2. The prefix cannot be resolved to a known country.

By default, unresolvable numbers return `'XX'`.

```php
// Returns 'MA' for local or unknown numbers
$code = PhoneCountryService::resolveCountryCode('0600000000', 'MA');
```

---

## Notes

**Auto-discovery** — The `PhoneCountryServiceProvider` is automatically registered via Laravel's package discovery. No manual setup is required.

**Performance** — Prefix lookup is optimized using longest-prefix matching. For high-volume workloads, consider caching resolved results:

```php
$code = Cache::remember("phone_country:{$phone}", 3600, fn () =>
    PhoneCountryService::resolveCountryCode($phone)
);
```

**Data accuracy** — The prefix map covers most countries globally. Longer, more specific prefixes (such as North American area codes) take priority over shorter ones to ensure correct resolution.

---

## Contributing

Contributions are welcome. To get started:

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit your changes: `git commit -m 'Add your feature'`
4. Push to the branch: `git push origin feature/your-feature`
5. Open a Pull Request.

Please ensure your changes are well-tested and consistent with the existing code style.

---

## License

The MIT License (MIT). See [LICENSE](LICENSE) for full details.
