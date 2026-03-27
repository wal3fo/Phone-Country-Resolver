# phone-country-resolver

[![Latest Version on Packagist](https://img.shields.io/packagist/v/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![Total Downloads](https://img.shields.io/packagist/dt/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)
[![License](https://img.shields.io/packagist/l/wal3fo/phone-country.svg?style=flat-square)](https://packagist.org/packages/wal3fo/phone-country)

A Laravel package to resolve **ISO 3166-1 alpha-2 country codes** from phone numbers — with AI-powered normalization, NANP disambiguation, fraud detection, rich metadata enrichment, and plain-language explanations.

Everything is zero-dependency and zero-configuration. No LLM API key required.

---

## What's New in v1.0.3

- **`analyze()`** — one method that runs all AI features and returns a `PhoneAnalysis` object
- **Smart normalization** — repairs messy real-world inputs before resolution
- **NANP disambiguation** — resolves `+1` to the correct country (US vs Canada vs Jamaica vs 20+ others) using area codes
- **Fraud signal detection** — classifies number type (mobile / landline / VOIP / toll-free / premium) and scores risk 0–100
- **Rich metadata** — timezone, currency, language, expected subscriber length, and length validity
- **Plain-language explanation** — human-readable description for dashboards and support tools
- **`analyzeMany()`** — batch version of `analyze()`
- All v1.x methods (`resolve()`, `resolveMany()`, `normalize()`, `resolveCountryCode()`) are fully unchanged

---

## Installation

```bash
composer require wal3fo/phone-country
```

Optionally publish the config:

```bash
php artisan vendor:publish --tag=phone-country-config
```

---

## AI Features — analyze()

`analyze()` is the new primary method. It runs all AI features in sequence and returns a `PhoneAnalysis` object.

```php
use Wal3fo\PhoneCountry\PhoneCountryService;

$analysis = PhoneCountryService::analyze('+18765551234');

// ── Disambiguation ──────────────────────────────────────────────
$analysis->disambiguatedCountryCode;   // 'JM'  (Jamaica, not US)
$analysis->disambiguationNote;         // 'Area code 876 is assigned to JM, not US.'
$analysis->countryCode();              // 'JM'  (best available code, disambiguation-aware)

// ── Fraud & Risk ────────────────────────────────────────────────
$analysis->riskLevel;                  // 'low'
$analysis->riskScore;                  // 0–100
$analysis->numberType;                 // 'mobile'
$analysis->fraudSignals;               // []

// ── Rich Metadata ────────────────────────────────────────────────
$analysis->timezone;                   // 'America/Jamaica'
$analysis->currency;                   // 'JMD'
$analysis->language;                   // 'en'
$analysis->subscriberLength;           // 7
$analysis->lengthValid;                // true

// ── Explanation ──────────────────────────────────────────────────
$analysis->explanation;
// "This appears to be a mobile number from Jamaica (dial code +1).
//  It was provided in standard international format.
//  Area code 876 is assigned to JM, not US.
//  The standardized E.164 format is +18765551234.
//  No fraud signals were detected."

// ── Safety check ─────────────────────────────────────────────────
$analysis->isSafe();                   // true (low risk + valid length + resolved)

// ── Full array for API responses ─────────────────────────────────
$analysis->toArray();
```

### Smart normalization

Messy inputs are automatically cleaned before resolution:

```php
$a = PhoneCountryService::analyze('(+212) 06-12.345.678');
$a->wasNormalized;        // true
$a->normalizedInput;      // '+212612345678'
$a->rawInput;             // '(+212) 06-12.345.678'
$a->result->countryCode;  // 'MA'

// UK-style trunk prefix
$b = PhoneCountryService::analyze('+44 (0)20 7946 0958');
$b->wasNormalized;        // true
$b->result->countryCode;  // 'GB'

// Label prefix (from contact books)
$c = PhoneCountryService::analyze('Phone: +33612345678');
$c->wasNormalized;        // true
$c->result->countryCode;  // 'FR'
```

### NANP disambiguation

The `+1` prefix is shared by 25+ countries. `analyze()` resolves the correct one:

```php
// Jamaica
PhoneCountryService::analyze('+18765551234')->disambiguatedCountryCode; // 'JM'

// Canada
PhoneCountryService::analyze('+14165551234')->disambiguatedCountryCode; // 'CA'

// Bahamas
PhoneCountryService::analyze('+12425551234')->disambiguatedCountryCode; // 'BS'

// Puerto Rico
PhoneCountryService::analyze('+17875551234')->disambiguatedCountryCode; // 'PR'

// Plain US number — no note, no overhead
PhoneCountryService::analyze('+12125551234')->disambiguatedCountryCode; // 'US'
```

### Fraud detection

```php
// Toll-free number
$a = PhoneCountryService::analyze('+18005551234');
$a->numberType;    // 'toll_free'
$a->riskLevel;     // 'medium'
$a->riskScore;     // 30
$a->fraudSignals;  // ['toll_free_range']

// Premium-rate number
$b = PhoneCountryService::analyze('+19005551234');
$b->numberType;    // 'premium'
$b->riskLevel;     // 'high'
$b->riskScore;     // 50

// Repeating pattern (test/fake number)
$c = PhoneCountryService::analyze('+212611111111');
$c->fraudSignals;  // ['repeating_digit_pattern']
$c->riskLevel;     // 'high'
```

### Rich metadata

```php
$a = PhoneCountryService::analyze('+212612345678');
$a->timezone;           // 'Africa/Casablanca'
$a->currency;           // 'MAD'
$a->language;           // 'ar'
$a->subscriberLength;   // 9
$a->lengthValid;        // true

$b = PhoneCountryService::analyze('+33612345678');
$b->timezone;           // 'Europe/Paris'
$b->currency;           // 'EUR'
$b->language;           // 'fr'
```

### Batch analysis

```php
$results = PhoneCountryService::analyzeMany([
    '+212612345678',
    '+18765551234',
    '(+44) (0)20 7946 0958',
]);

foreach ($results as $analysis) {
    echo $analysis->countryCode() . ': ' . $analysis->riskLevel . PHP_EOL;
}
// MA: low
// JM: low
// GB: low
```

---

## v1.x Methods (fully unchanged)

### resolve()

```php
$result = PhoneCountryService::resolve('+212612345678');

$result->countryCode;  // 'MA'
$result->countryName;  // 'Morocco'
$result->dialCode;     // '+212'
$result->isResolved;   // true
$result->format;       // 'international'
$result->e164;         // '+212612345678'
$result->isValid();    // true
$result->toArray();
echo $result;          // 'MA'
```

### resolveMany()

```php
$results = PhoneCountryService::resolveMany(['+212612345678', '+33612345678'], 'MA');
```

### normalize()

```php
PhoneCountryService::normalize('+212612345678');       // '+212612345678'
PhoneCountryService::normalize('00212612345678');      // '+212612345678'
PhoneCountryService::normalize('0612345678', 'MA');    // '+212612345678'
PhoneCountryService::normalize('0612345678');          // null
```

### Validation rule

```php
use Wal3fo\PhoneCountry\Rules\PhoneCountryRule;

'phone' => ['required', new PhoneCountryRule()]
'phone' => ['required', new PhoneCountryRule('MA')]
'phone' => ['required', new PhoneCountryRule(['MA', 'FR', 'ES'])]
'phone' => ['required', new PhoneCountryRule('MA', strict: true)]
```

### resolveCountryCode() — backward compatible

```php
PhoneCountryService::resolveCountryCode('+212612345678');       // 'MA'
PhoneCountryService::resolveCountryCode('0612345678', 'MA');    // 'MA'
PhoneCountryService::resolveCountryCode('0612345678');          // 'XX'
```

---

## PhoneAnalysis object reference

| Property | Type | Description |
|---|---|---|
| `result` | `PhoneResult` | Underlying resolved result |
| `rawInput` | `string` | Original input before normalization |
| `normalizedInput` | `string` | Cleaned input after normalization |
| `wasNormalized` | `bool` | Whether the input was repaired |
| `disambiguatedCountryCode` | `string` | Best country code (NANP-aware) |
| `disambiguationNote` | `?string` | Explanation if NANP was disambiguated |
| `riskLevel` | `string` | `'low'` / `'medium'` / `'high'` |
| `riskScore` | `int` | 0–100 |
| `numberType` | `string` | `'mobile'` / `'landline'` / `'voip'` / `'toll_free'` / `'premium'` / `'unknown'` |
| `fraudSignals` | `string[]` | Detected signal labels |
| `timezone` | `?string` | IANA timezone |
| `currency` | `?string` | ISO 4217 currency code |
| `language` | `?string` | BCP 47 language tag |
| `subscriberLength` | `?int` | Expected digits after dial code |
| `lengthValid` | `bool` | Whether actual length matches expected |
| `explanation` | `string` | Plain-language description |

Methods: `countryCode()`, `isSafe()`, `toArray()`

---

## File structure

```
src/
├── PhoneCountryService.php       # Main service — all public methods
├── PhoneCountryServiceProvider.php
├── PhoneResult.php               # Value object from resolve()
├── PhoneAnalysis.php             # Value object from analyze()
├── Rules/
│   └── PhoneCountryRule.php      # Laravel validation rule
└── AI/
    ├── PhoneNormalizer.php       # Smart input normalization
    ├── NanpDisambiguator.php     # +1 shared-prefix resolution
    ├── FraudDetector.php         # Risk scoring & type classification
    ├── MetadataEnricher.php      # Timezone / currency / language / length
    └── PhoneExplainer.php        # Plain-language explanation generator
config/
└── phone-country.php
```

---

## Configuration

```php
// config/phone-country.php
return [
    'default_country' => env('PHONE_COUNTRY_DEFAULT', 'XX'),
    'unknown_code'    => 'XX',
];
```

```env
PHONE_COUNTRY_DEFAULT=MA
```

---

## Performance

For high-volume workloads, cache `analyze()` results:

```php
$analysis = Cache::remember("phone_analysis:{$phone}", 3600, fn () =>
    PhoneCountryService::analyze($phone)
);
```

---

## Contributing

1. Fork the repository.
2. Create a feature branch: `git checkout -b feature/your-feature`
3. Commit: `git commit -m 'Add your feature'`
4. Push: `git push origin feature/your-feature`
5. Open a Pull Request.

---

## Changelog

### v1.0.3
- Added `analyze()` returning `PhoneAnalysis` with all AI features
- Added `analyzeMany()` for batch analysis
- Added `src/AI/PhoneNormalizer.php` — smart input normalization
- Added `src/AI/NanpDisambiguator.php` — full NANP area-code map (25+ countries)
- Added `src/AI/FraudDetector.php` — risk scoring, type classification, signal detection
- Added `src/AI/MetadataEnricher.php` — timezone/currency/language/length for 180+ countries
- Added `src/AI/PhoneExplainer.php` — plain-language explanation generator
- Added `src/PhoneAnalysis.php` value object

### v1.0.2
- Added `PhoneResult`, `resolveMany()`, `normalize()`, `PhoneCountryRule`, config support

### v1.0.1
- Initial release with `resolveCountryCode()` and longest-prefix matching

---

## License

The MIT License (MIT). See [LICENSE](LICENSE) for full details.
