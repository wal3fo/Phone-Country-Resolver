# phone-country-resolver

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![Total Downloads](https://img.shields.io/packagist/dt/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![License](https://img.shields.io/packagist/l/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)

A lightweight Laravel package to resolve **ISO 3166-1 alpha-2 country codes** from phone numbers. Supports international prefixes, double-zero formats, numeric-only inputs, and local number fallbacks — with zero configuration required.

---

## What's New in v0.2.0

- **`PhoneResult` object** — `resolve()` now returns a rich result with country name, dial code, E.164 format, and more
- **Laravel validation rule** — `PhoneCountryRule` integrates directly with Laravel's validator
- **Batch resolution** — `resolveMany()` resolves an array of numbers in one call
- **E.164 normalization** — `normalize()` returns a standardized phone string for database storage
- **Publishable config** — set a global default country once via `config/phone-country.php`
- `resolveCountryCode()` is kept as a fully backward-compatible alias — nothing breaks

---

## Features

- Resolves country codes from international prefixes (e.g. `+1`, `0044`, `212`)
- Returns a rich `PhoneResult` object with name, dial code, format, and E.164
- Built-in Laravel validation rule with country allowlist and strict mode
- Handles local phone formats starting with `0` via a configurable fallback
- Longest-prefix matching for accurate resolution of overlapping codes (e.g. NANP)
- Registered as a singleton — supports both static calls and dependency injection
- Auto-discovered by Laravel — no manual provider registration needed

---

## Installation

```bash
composer require wal3fo/phone-country
```

Optionally publish the config file to set a global default country:

```bash
php artisan vendor:publish --tag=phone-country-config
```

---

## Usage

### resolve() — recommended

`resolve()` returns a `PhoneResult` object with everything you need:

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

$result = PhoneCountryService::resolve('+212612345678');

$result->countryCode;  // 'MA'
$result->countryName;  // 'Morocco'
$result->dialCode;     // '+212'
$result->isResolved;   // true
$result->format;       // 'international'
$result->e164;         // '+212612345678'
$result->isValid();    // true
$result->toArray();    // ready for API responses

// Still works as a string
echo $result;          // 'MA'
```

With a local number fallback:

```php
$result = PhoneCountryService::resolve('0612345678', 'MA');
$result->countryCode;  // 'MA'
$result->format;       // 'local'
$result->e164;         // '+212612345678'
```

### resolveMany() — batch resolution

```php
$results = PhoneCountryService::resolveMany([
    '+212612345678',
    '+33612345678',
    '0612345678',
], 'MA');

// Returns PhoneResult[] in the same order
```

### normalize() — E.164 for database storage

```php
PhoneCountryService::normalize('+212612345678');      // '+212612345678'
PhoneCountryService::normalize('00212612345678');     // '+212612345678'
PhoneCountryService::normalize('0612345678', 'MA');   // '+212612345678'
PhoneCountryService::normalize('0612345678');         // null (unresolvable)
```

### Validation Rule

Use `PhoneCountryRule` directly inside your Laravel form requests or controllers:

```php
use Wal3fo\PhoneCountry\Rules\PhoneCountryRule;

// Accept any valid international number
'phone' => ['required', new PhoneCountryRule()]

// Accept international or local numbers, with Morocco as fallback
'phone' => ['required', new PhoneCountryRule('MA')]

// Restrict to specific countries
'phone' => ['required', new PhoneCountryRule(['MA', 'FR', 'ES'])]

// Strict mode: reject local 0xxx formats, require full international format
'phone' => ['required', new PhoneCountryRule('MA', strict: true)]
```

Example in a Form Request:

```php
use Wal3fo\PhoneCountry\Rules\PhoneCountryRule;

class RegisterRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'name'  => ['required', 'string'],
            'phone' => ['required', 'string', new PhoneCountryRule('MA')],
        ];
    }
}
```

### Dependency Injection

The service is registered as a singleton and can be injected directly:

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

class UserProfileController extends Controller
{
    public function __construct(
        protected PhoneCountryService $phoneService
    ) {}

    public function update(Request $request)
    {
        $result = $this->phoneService->resolve($request->phone_number);

        $user->update([
            'phone'        => $result->e164,
            'country_code' => $result->countryCode,
        ]);
    }
}
```

### resolveCountryCode() — backward compatible

The original method still works exactly as before:

```php
PhoneCountryService::resolveCountryCode('+212612345678');       // 'MA'
PhoneCountryService::resolveCountryCode('0612345678', 'MA');   // 'MA'
PhoneCountryService::resolveCountryCode('0612345678');         // 'XX'
```

---

## Examples

| Input | Country Code | Country Name | Format |
|-------|-------------|--------------|--------|
| `+212612345678` | `MA` | Morocco | international |
| `00212612345678` | `MA` | Morocco | international |
| `212612345678` | `MA` | Morocco | numeric |
| `0612345678` + `'MA'` | `MA` | Morocco | local |
| `0612345678` | `XX` | Unknown | local |
| `+1 242 123 4567` | `BS` | Bahamas | international |
| `+33612345678` | `FR` | France | international |

---

## Configuration

Publish the config file to set global defaults:

```bash
php artisan vendor:publish --tag=phone-country-config
```

```php
// config/phone-country.php
return [
    'default_country' => env('PHONE_COUNTRY_DEFAULT', 'XX'),
    'unknown_code'    => 'XX',
];
```

Or set it in your `.env`:

```env
PHONE_COUNTRY_DEFAULT=MA
```

---

## Notes

**Auto-discovery** — The `PhoneCountryServiceProvider` is automatically registered via Laravel's package discovery. No manual setup is required.

**Performance** — Prefix lookup is optimized using longest-prefix matching. For high-volume workloads, consider caching resolved results:

```php
$result = Cache::remember("phone_country:{$phone}", 3600, fn () =>
    PhoneCountryService::resolve($phone)
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

## Changelog

### v1.0.2
- Added `PhoneResult` value object returned by `resolve()`
- Added `resolveMany()` for batch resolution
- Added `normalize()` for E.164 output
- Added `PhoneCountryRule` Laravel validation rule with allowlist and strict mode
- Added publishable config with `default_country` support
- `resolveCountryCode()` kept as backward-compatible alias

### v1.0.1
- Initial release with `resolveCountryCode()` and longest-prefix matching

---

## License

The MIT License (MIT). See [LICENSE](LICENSE) for full details.