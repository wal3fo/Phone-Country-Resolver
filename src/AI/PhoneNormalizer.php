<?php

namespace Wal3fo\PhoneCountry\AI;

/**
 * Cleans and repairs malformed phone number inputs before prefix resolution.
 *
 * Handles real-world noise such as:
 *   "(+212) 06-12.345.678"
 *   "00 44 (0)20 7946 0958"
 *   "+33 (0) 6 12 34 56 78"
 *   "Phone: +1-800-555-0123"
 *   "212-612-345-678"        ← digits + dashes, no explicit prefix char
 */
class PhoneNormalizer
{
    /**
     * Attempt to normalize a raw phone string into a clean, resolvable form.
     *
     * Returns [$normalized, $wasChanged]:
     *   $normalized  — the cleaned string ready for PhoneCountryService::resolve()
     *   $wasChanged  — true if we had to repair/rewrite the input
     */
    public static function normalize(string $raw): array
    {
        $original = $raw;
        $input    = $raw;

        // 1. Strip common label prefixes people paste from contact books / forms
        $input = preg_replace('/^(phone\s*[:：]?\s*|tel\s*[:：]?\s*|mobile\s*[:：]?\s*|tél\s*[:：]?\s*)/iu', '', trim($input));

        // 2. Detect and preserve the international prefix character/sequence BEFORE stripping
        $hasPlus    = str_contains($input, '+');
        $hasDoubleZero = preg_match('/^\s*00/', preg_replace('/[^0-9]/', '', $input));

        // 3. UK-style "(0)" trunk prefix inside an international number — remove it
        //    e.g. "+44 (0)20 7946 0958" → "+44 20 7946 0958"
        $input = preg_replace('/\(0\)/', '', $input);

        // 4. French-style "(0)" — same pattern, different spacing
        $input = preg_replace('/\(\s*0\s*\)/', '', $input);

        // 5. Collapse all non-digit characters except the leading + sign
        //    We preserve the first + if present.
        if ($hasPlus) {
            // Keep the first + and strip everything else non-numeric
            $input = '+' . preg_replace('/[^0-9]/', '', $input);
        } else {
            $input = preg_replace('/[^0-9]/', '', $input);

            // Restore double-zero prefix so PhoneCountryService::clean() handles it
            if ($hasDoubleZero && !str_starts_with($input, '00')) {
                // The 00 was already in the digits, nothing to do
            }
        }

        // 6. If we collapsed to pure digits and it looks like it might have a
        //    country code embedded without a prefix char, leave it — the service
        //    handles numeric-only inputs natively.

        $wasChanged = trim($input) !== trim($original);

        return [$input, $wasChanged];
    }
}
