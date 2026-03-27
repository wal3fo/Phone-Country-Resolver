<?php

namespace Wal3fo\PhoneCountry;

/**
 * Rich result returned by PhoneCountryService::analyze().
 *
 * Extends the base PhoneResult with AI-powered signals:
 *   - Smart normalization (messy input repair)
 *   - NANP disambiguation (which +1 country exactly)
 *   - Fraud / risk scoring
 *   - Rich metadata (timezone, currency, language)
 *   - Human-readable explanation
 */
class PhoneAnalysis
{
    public function __construct(
        /** The underlying resolved phone result */
        public readonly PhoneResult $result,

        /** The original raw input before normalization */
        public readonly string $rawInput,

        /** The cleaned input that was actually resolved */
        public readonly string $normalizedInput,

        /** Whether the input required AI normalization to be parsed */
        public readonly bool $wasNormalized,

        // ── Disambiguation ────────────────────────────────────────────────────

        /**
         * Disambiguated country code (overrides $result->countryCode for shared prefixes).
         * E.g. '+1 876 ...' → 'JM' instead of the generic 'US'.
         */
        public readonly string $disambiguatedCountryCode,

        /** Human-readable disambiguation note, or null if no ambiguity existed. */
        public readonly ?string $disambiguationNote,

        // ── Fraud / Risk ──────────────────────────────────────────────────────

        /** Risk level: 'low' | 'medium' | 'high' */
        public readonly string $riskLevel,

        /** Risk score 0–100 (0 = clean, 100 = very suspicious) */
        public readonly int $riskScore,

        /** Detected number type: 'mobile' | 'landline' | 'voip' | 'toll_free' | 'premium' | 'unknown' */
        public readonly string $numberType,

        /** List of specific fraud signals detected, e.g. ['toll_free_range', 'voip_pattern'] */
        public readonly array $fraudSignals,

        // ── Rich Metadata ─────────────────────────────────────────────────────

        /** Primary timezone identifier, e.g. 'Africa/Casablanca' */
        public readonly ?string $timezone,

        /** ISO 4217 currency code, e.g. 'MAD' */
        public readonly ?string $currency,

        /** BCP 47 primary language tag, e.g. 'ar' */
        public readonly ?string $language,

        /** Expected subscriber number length (digits after dial code) */
        public readonly ?int $subscriberLength,

        /** Whether the subscriber number length looks correct */
        public readonly bool $lengthValid,

        // ── Explanation ───────────────────────────────────────────────────────

        /** Plain-language description of what this number is */
        public readonly string $explanation,
    ) {}

    /**
     * Convenience: the best country code to use (disambiguation-aware).
     */
    public function countryCode(): string
    {
        return $this->disambiguatedCountryCode !== 'XX'
            ? $this->disambiguatedCountryCode
            : $this->result->countryCode;
    }

    /**
     * True if the number is safe to accept (low risk, valid length, resolved).
     */
    public function isSafe(): bool
    {
        return $this->result->isValid()
            && $this->riskLevel === 'low'
            && $this->lengthValid;
    }

    /**
     * Serialize to array — ready for API responses.
     */
    public function toArray(): array
    {
        return [
            'result'                    => $this->result->toArray(),
            'raw_input'                 => $this->rawInput,
            'normalized_input'          => $this->normalizedInput,
            'was_normalized'            => $this->wasNormalized,
            'disambiguated_country'     => $this->disambiguatedCountryCode,
            'disambiguation_note'       => $this->disambiguationNote,
            'risk_level'                => $this->riskLevel,
            'risk_score'                => $this->riskScore,
            'number_type'               => $this->numberType,
            'fraud_signals'             => $this->fraudSignals,
            'timezone'                  => $this->timezone,
            'currency'                  => $this->currency,
            'language'                  => $this->language,
            'subscriber_length'         => $this->subscriberLength,
            'length_valid'              => $this->lengthValid,
            'explanation'               => $this->explanation,
            'is_safe'                   => $this->isSafe(),
        ];
    }
}
