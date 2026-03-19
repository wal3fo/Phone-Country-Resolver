# Phone Country Resolver

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![Total Downloads](https://img.shields.io/packagist/dt/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![License](https://img.shields.io/packagist/l/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)

A professional, lightweight Laravel package to resolve ISO 3166-1 alpha-2 country codes from phone numbers. This package supports various international and local phone number formats.

## Features

- Resolves country codes from international prefixes (e.g., `+1`, `0044`).
- Handles local phone formats starting with `0`.
- Customizable fallback for local or unresolved numbers.
- Seamless integration with Laravel's Service Container.

## Installation

You can install the package via Composer:

```bash
composer require wal3fo/phone-country
```

### Local Development

If you're working with a local version of this package, add the repository to your Laravel project's `composer.json`:

```json
"repositories": [
    {
        "type": "path",
        "url": "../Phone-Country-Resolver"
    }
],
```

Then require it:

```bash
composer require wal3fo/phone-country
```

## Usage

### 1. Static Method Usage

You can call the `resolveCountryCode` method statically from anywhere in your application.

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

// Basic usage
$country = PhoneCountryService::resolveCountryCode('+212612345678'); // Returns 'MA'

// With custom local/fallback country code
$country = PhoneCountryService::resolveCountryCode('0612345678', 'MA'); // Returns 'MA'
```

### 2. Dependency Injection

The package automatically registers a singleton in the Laravel container. You can inject the service into your controllers or other classes.

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

class UserProfileController extends Controller
{
    protected $phoneService;

    public function __construct(PhoneCountryService $phoneService)
    {
        $this->phoneService = $phoneService;
    }

    public function update(Request $request)
    {
        $countryCode = $this->phoneService->resolveCountryCode($request->phone_number);
        // ...
    }
}
```

## Examples

The service is designed to handle multiple common phone number formats:

| Input Format | Resolved Country | Note |
|--------------|------------------|------|
| `+212612345678` | `MA` | Standard international format |
| `00212612345678` | `MA` | International prefix `00` |
| `212612345678` | `MA` | Numeric-only international |
| `0612345678` | `XX` (Default) | Local format (returns fallback) |
| `0612345678` | `MA` | Local format with `MA` as fallback |
| `+1 242 123 4567`| `BS` | Formatted international (Bahamas) |

## Configuration

The `resolveCountryCode` method accepts an optional second parameter, `localCountryCode`, which serves as a fallback for:
1. Local numbers (starting with `0`).
2. Numbers with prefixes that cannot be resolved.

By default, it returns `'XX'`.

```php
// If the number is local or unknown, return 'MA'
$code = PhoneCountryService::resolveCountryCode('0600000000', 'MA');
```

## Notes & Tips

- **Auto-Discovery**: This package uses Laravel's auto-discovery. The `PhoneCountryServiceProvider` is registered automatically.
- **Performance**: The prefix lookup is optimized. For high-volume applications, consider caching the results of frequently resolved numbers to reduce processing overhead.
- **Data Accuracy**: The prefix mapping covers most countries, prioritizing longer prefixes (e.g., North American Area Codes) to ensure accuracy.

## Contribution & License

### Contribution

Contributions are welcome! Please feel free to submit Pull Requests or open issues on GitHub.

1. Fork the repository.
2. Create your feature branch (`git checkout -b feature/AmazingFeature`).
3. Commit your changes (`git commit -m 'Add some AmazingFeature'`).
4. Push to the branch (`git push origin feature/AmazingFeature`).
5. Open a Pull Request.

### License

The MIT License (MIT). Please see [License File](LICENSE) for more information.
