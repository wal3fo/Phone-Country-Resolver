<?php

namespace Wal3fo\PhoneCountry\AI;

/**
 * Scores a phone number for fraud / abuse signals.
 *
 * Detection approach:
 *   - Number type classification (mobile / landline / VOIP / toll-free / premium)
 *   - Known high-risk prefix patterns
 *   - Subscriber number length plausibility
 *   - Structural anomaly detection
 *
 * Returns a risk score (0–100) and a list of signal labels.
 */
class FraudDetector
{
    // ── Toll-free prefixes (per country) ─────────────────────────────────────
    private static array $tollFreePrefixes = [
        // NANP toll-free area codes
        '1800', '1833', '1844', '1855', '1866', '1877', '1888',
        // UK freephone
        '44800', '44808',
        // France
        '33800',
        // Germany
        '49800',
        // Australia
        '611800',
        // Morocco
        '212801', '212802',
    ];

    // ── Premium-rate prefixes ─────────────────────────────────────────────────
    private static array $premiumPrefixes = [
        // NANP premium
        '1900',
        // UK premium
        '44909', '44905', '44871', '44872', '44873',
        // France
        '33899', '33892', '33891',
        // Germany
        '49900', '49901',
    ];

    // ── Known VOIP range indicators ───────────────────────────────────────────
    // These are area codes / prefixes heavily used by VOIP providers
    // and commonly seen in spam / fraud registrations.
    private static array $voipIndicators = [
        // NANP VOIP-heavy area codes
        '1646', '1347', '1929', '1917', // NY VOIP-dense
        '1669', '1408',                 // Silicon Valley VOIP
        '1213', '1323',                 // LA VOIP-heavy
        '1720', '1303',                 // Denver VOIP
        // UK VOIP ranges
        '4456',
        // Known VOIP country codes that rarely have real mobile subscribers
        '883', '882',   // ITU international VOIP
    ];

    /**
     * Analyse a phone number and return fraud signals.
     *
     * @param  string  $cleanNumber    Digits only after prefix normalisation
     * @param  string  $countryCode    Resolved ISO country code
     * @param  string  $dialCode       E.g. '+1', '+44'
     * @return array{score: int, level: string, type: string, signals: string[]}
     */
    public static function analyze(string $cleanNumber, string $countryCode, string $dialCode): array
    {
        $signals = [];
        $score   = 0;

        $dialDigits = preg_replace('/[^0-9]/', '', $dialCode);
        $prefix4    = substr($dialDigits . $cleanNumber, 0, 4);
        $prefix5    = substr($dialDigits . $cleanNumber, 0, 5);
        $prefix6    = substr($dialDigits . $cleanNumber, 0, 6);

        // ── Detect number type ────────────────────────────────────────────────

        $type = self::classifyType($cleanNumber, $dialDigits, $prefix4, $prefix5, $prefix6);

        // ── Scoring based on type ─────────────────────────────────────────────

        if ($type === 'toll_free') {
            $signals[] = 'toll_free_range';
            $score    += 30;
        }

        if ($type === 'premium') {
            $signals[] = 'premium_rate_range';
            $score    += 50;
        }

        if ($type === 'voip') {
            $signals[] = 'voip_pattern';
            $score    += 25;
        }

        // ── Structural anomalies ──────────────────────────────────────────────

        // Repeating digit patterns (e.g. 555-1111, 000-0000) — test numbers
        $subscriberDigits = ltrim($cleanNumber, $dialDigits);
        if (preg_match('/^(\d)\1{5,}$/', $subscriberDigits)) {
            $signals[] = 'repeating_digit_pattern';
            $score    += 40;
        }

        // Sequential digits (12345678, 87654321) — synthetic numbers
        if (self::isSequential($subscriberDigits)) {
            $signals[] = 'sequential_digit_pattern';
            $score    += 35;
        }

        // US/NANP: 555-0100–555-0199 are reserved for fictional use
        if (str_starts_with($cleanNumber, '1') && preg_match('/^1\d{3}5550[01]\d{2}$/', $cleanNumber)) {
            $signals[] = 'fictional_reserved_range';
            $score    += 80;
        }

        // Unknown country is a strong signal
        if ($countryCode === 'XX') {
            $signals[] = 'unresolvable_country';
            $score    += 30;
        }

        // Clamp score to 0–100
        $score = min(100, max(0, $score));

        $level = match(true) {
            $score >= 60 => 'high',
            $score >= 30 => 'medium',
            default      => 'low',
        };

        return [
            'score'   => $score,
            'level'   => $level,
            'type'    => $type,
            'signals' => $signals,
        ];
    }

    // ── Private helpers ───────────────────────────────────────────────────────

    private static function classifyType(
        string $cleanNumber,
        string $dialDigits,
        string $prefix4,
        string $prefix5,
        string $prefix6
    ): string {
        foreach (self::$premiumPrefixes as $p) {
            if (str_starts_with($dialDigits . $cleanNumber, $p)) {
                return 'premium';
            }
        }

        foreach (self::$tollFreePrefixes as $p) {
            if (str_starts_with($dialDigits . $cleanNumber, $p)) {
                return 'toll_free';
            }
        }

        foreach (self::$voipIndicators as $p) {
            if (str_starts_with($dialDigits . $cleanNumber, $p)) {
                return 'voip';
            }
        }

        // Heuristic: mobile numbers in most countries start with 6, 7, or 8
        // after the country code. This is a rough signal only.
        $subscriber = ltrim(substr($dialDigits . $cleanNumber, strlen($dialDigits)), '0');
        $firstDigit = $subscriber[0] ?? '';

        if (in_array($firstDigit, ['6', '7'], true)) {
            return 'mobile';
        }

        if (in_array($firstDigit, ['2', '3', '4', '5'], true)) {
            return 'landline';
        }

        return 'unknown';
    }

    private static function isSequential(string $digits): bool
    {
        if (strlen($digits) < 6) {
            return false;
        }

        $asc  = true;
        $desc = true;

        for ($i = 1; $i < strlen($digits); $i++) {
            $diff = (int) $digits[$i] - (int) $digits[$i - 1];
            if ($diff !== 1)  $asc  = false;
            if ($diff !== -1) $desc = false;
        }

        return $asc || $desc;
    }
}
