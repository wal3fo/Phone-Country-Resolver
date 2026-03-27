<?php

namespace Wal3fo\PhoneCountry\AI;

use Wal3fo\PhoneCountry\PhoneResult;

/**
 * Generates a plain-language human explanation for a phone number.
 *
 * This is a deterministic generator — no LLM required. It combines
 * the resolved metadata to produce a natural, readable sentence
 * useful in admin dashboards and customer support tools.
 */
class PhoneExplainer
{
    /**
     * Generate a plain-language explanation.
     *
     * @param  PhoneResult  $result      The resolved phone result
     * @param  string       $numberType  Classified type (mobile|landline|voip|toll_free|premium|unknown)
     * @param  string       $riskLevel   Risk level (low|medium|high)
     * @param  array        $signals     Detected fraud signals
     * @param  bool         $wasNormalized  Whether the input was repaired
     * @param  ?string      $disambiguationNote  Note from NANP disambiguation, if any
     */
    public static function explain(
        PhoneResult $result,
        string $numberType,
        string $riskLevel,
        array $signals,
        bool $wasNormalized,
        ?string $disambiguationNote,
    ): string {
        $parts = [];

        // ── Base description ──────────────────────────────────────────────────

        if (! $result->isValid()) {
            $parts[] = 'This phone number could not be resolved to a known country.';
        } else {
            $typeLabel = self::typeLabel($numberType);
            $parts[]   = "This appears to be a {$typeLabel} number from {$result->countryName} (dial code {$result->dialCode}).";
        }

        // ── Format note ───────────────────────────────────────────────────────

        if ($result->format === 'local') {
            $parts[] = 'It was provided in local format and resolved using the configured country fallback.';
        } elseif ($result->format === 'international') {
            $parts[] = 'It was provided in standard international format.';
        } elseif ($result->format === 'numeric') {
            $parts[] = 'It was provided as a numeric-only string without an explicit prefix character.';
        }

        // ── Normalization note ────────────────────────────────────────────────

        if ($wasNormalized) {
            $parts[] = 'The input contained non-standard formatting and was automatically cleaned before resolution.';
        }

        // ── Disambiguation note ───────────────────────────────────────────────

        if ($disambiguationNote !== null) {
            $parts[] = $disambiguationNote;
        }

        // ── E.164 ─────────────────────────────────────────────────────────────

        if ($result->e164 !== '') {
            $parts[] = "The standardized E.164 format is {$result->e164}.";
        }

        // ── Risk summary ──────────────────────────────────────────────────────

        if ($riskLevel === 'low') {
            $parts[] = 'No fraud signals were detected.';
        } elseif ($riskLevel === 'medium') {
            $signalList = implode(', ', array_map(fn($s) => str_replace('_', ' ', $s), $signals));
            $parts[]    = "This number has medium risk — the following signals were detected: {$signalList}.";
        } elseif ($riskLevel === 'high') {
            $signalList = implode(', ', array_map(fn($s) => str_replace('_', ' ', $s), $signals));
            $parts[]    = "This number has HIGH risk and should be treated with caution — signals detected: {$signalList}.";
        }

        return implode(' ', $parts);
    }

    private static function typeLabel(string $type): string
    {
        return match($type) {
            'mobile'    => 'mobile',
            'landline'  => 'landline',
            'voip'      => 'VOIP',
            'toll_free' => 'toll-free',
            'premium'   => 'premium-rate',
            default     => 'phone',
        };
    }
}
