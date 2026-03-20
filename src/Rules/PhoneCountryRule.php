<?php

namespace Wal3fo\PhoneCountry\Rules;

use Closure;
use Illuminate\Contracts\Validation\ValidationRule;
use Wal3fo\PhoneCountry\PhoneCountryService;

/**
 * Validates that a phone number resolves to a real country.
 *
 * Usage:
 *
 *   // Accept any internationally-resolvable number
 *   'phone' => ['required', new PhoneCountryRule()]
 *
 *   // Accept international OR local numbers for Morocco
 *   'phone' => ['required', new PhoneCountryRule('MA')]
 *
 *   // Restrict to a specific country (strict mode)
 *   'phone' => ['required', new PhoneCountryRule('MA', strict: true)]
 *
 *   // Accept only numbers from a list of countries
 *   'phone' => ['required', new PhoneCountryRule(['MA', 'FR', 'ES'])]
 */
class PhoneCountryRule implements ValidationRule
{
    /**
     * @param string|string[]|null $countries  ISO code(s) to allow, or null for any country.
     * @param bool                 $strict     When true, the resolved country must match $countries exactly.
     */
    public function __construct(
        private readonly string|array|null $countries = null,
        private readonly bool $strict = false,
    ) {}

    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (! is_string($value)) {
            $fail("The :attribute must be a string.");
            return;
        }

        $localFallback = $this->primaryCountry() ?? 'XX';
        $result = PhoneCountryService::resolve($value, $localFallback);

        // Must resolve to a real country
        if (! $result->isValid()) {
            $fail("The :attribute is not a valid international phone number.");
            return;
        }

        // If specific countries are required, check membership
        if ($this->countries !== null) {
            $allowed = array_map('strtoupper', (array) $this->countries);

            if (! in_array($result->countryCode, $allowed, true)) {
                $names = implode(', ', array_map(
                    fn ($c) => PhoneCountryService::getCountryMeta($c)[0] ?? $c,
                    $allowed
                ));
                $fail("The :attribute must be a phone number from: {$names}.");
                return;
            }
        }

        // Strict mode: the number format must be international (not a local 0xxx number)
        if ($this->strict && $result->format === 'local') {
            $fail("The :attribute must be in international format (e.g. {$result->dialCode}...).");
        }
    }

    /**
     * Return the primary country from the countries parameter (first entry or the string itself).
     */
    private function primaryCountry(): ?string
    {
        if (is_string($this->countries)) {
            return $this->countries;
        }

        if (is_array($this->countries) && ! empty($this->countries)) {
            return $this->countries[0];
        }

        return null;
    }
}
