# Phone Country Resolver

A standalone Laravel package to resolve ISO 3166-1 alpha-2 country codes from phone numbers.

## Installation

### Local Installation

If you want to use this package locally without publishing it to Packagist:

1. In your Laravel project's `composer.json`, add the repository:

```json
"repositories": [
    {
        "type": "path",
        "url": "../path/to/Phone-Country-Resolver"
    }
],
```

2. Then run:

```bash
composer require wal3fo/phone-country
```

## Usage

### Static Call

You can use the static method directly:

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

$countryCode = PhoneCountryService::resolveCountryCode('+1 242 123 4567');
// Output: BS
```

### Dependency Injection

Or resolve it from the Laravel container:

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

public function __construct(PhoneCountryService $phoneService)
{
    $this->phoneService = $phoneService;
}

public function getCountry($number)
{
    return $this->phoneService->resolveCountryCode($number);
}
```

## License

MIT
