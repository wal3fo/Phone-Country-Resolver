<?php

namespace Wal3fo\PhoneCountry;

/**
 * Resolves country information from phone numbers.
 *
 * ── Existing API (unchanged) ─────────────────────────────────────────────
 *   PhoneCountryService::resolveCountryCode('+212612345678')  // → 'MA'
 *
 * ── New rich API ─────────────────────────────────────────────────────────
 *   $result = PhoneCountryService::analyze('+212612345678');
 *   $result->countryName;          // 'Morocco'
 *   $result->flag;                 // '🇲🇦'
 *   $result->toArray();            // full associative array
 *   $result->toJson();             // JSON string
 *   $result->toHtml();             // self-contained HTML card
 */
class PhoneCountryService
{
    // ── Prefix map ───────────────────────────────────────────────────────
    // Keyed by numeric prefix string (longest-match wins).
    // Covers ITU-T E.164 calling codes for all member states.
    private static array $prefixMap = [
        // ── 1-digit ──────────────────────────────────────────────────
        '1'   => 'US',  // NANP – see also 3-digit overrides below
        '7'   => 'RU',

        // ── 2-digit ──────────────────────────────────────────────────
        '20'  => 'EG', '27'  => 'ZA', '30'  => 'GR', '31'  => 'NL',
        '32'  => 'BE', '33'  => 'FR', '34'  => 'ES', '36'  => 'HU',
        '39'  => 'IT', '40'  => 'RO', '41'  => 'CH', '43'  => 'AT',
        '44'  => 'GB', '45'  => 'DK', '46'  => 'SE', '47'  => 'NO',
        '48'  => 'PL', '49'  => 'DE', '51'  => 'PE', '52'  => 'MX',
        '53'  => 'CU', '54'  => 'AR', '55'  => 'BR', '56'  => 'CL',
        '57'  => 'CO', '58'  => 'VE', '60'  => 'MY', '61'  => 'AU',
        '62'  => 'ID', '63'  => 'PH', '64'  => 'NZ', '65'  => 'SG',
        '66'  => 'TH', '81'  => 'JP', '82'  => 'KR', '84'  => 'VN',
        '86'  => 'CN', '90'  => 'TR', '91'  => 'IN', '92'  => 'PK',
        '93'  => 'AF', '94'  => 'LK', '95'  => 'MM', '98'  => 'IR',

        // ── 3-digit ──────────────────────────────────────────────────
        '212' => 'MA', '213' => 'DZ', '216' => 'TN', '218' => 'LY',
        '220' => 'GM', '221' => 'SN', '222' => 'MR', '223' => 'ML',
        '224' => 'GN', '225' => 'CI', '226' => 'BF', '227' => 'NE',
        '228' => 'TG', '229' => 'BJ', '230' => 'MU', '231' => 'LR',
        '232' => 'SL', '233' => 'GH', '234' => 'NG', '235' => 'TD',
        '236' => 'CF', '237' => 'CM', '238' => 'CV', '239' => 'ST',
        '240' => 'GQ', '241' => 'GA', '242' => 'CG', '243' => 'CD',
        '244' => 'AO', '245' => 'GW', '246' => 'IO', '247' => 'AC',
        '248' => 'SC', '249' => 'SD', '250' => 'RW', '251' => 'ET',
        '252' => 'SO', '253' => 'DJ', '254' => 'KE', '255' => 'TZ',
        '256' => 'UG', '257' => 'BI', '258' => 'MZ', '260' => 'ZM',
        '261' => 'MG', '262' => 'RE', '263' => 'ZW', '264' => 'NA',
        '265' => 'MW', '266' => 'LS', '267' => 'BW', '268' => 'SZ',
        '269' => 'KM', '290' => 'SH', '291' => 'ER', '297' => 'AW',
        '298' => 'FO', '299' => 'GL',
        '350' => 'GI', '351' => 'PT', '352' => 'LU', '353' => 'IE',
        '354' => 'IS', '355' => 'AL', '356' => 'MT', '357' => 'CY',
        '358' => 'FI', '359' => 'BG', '370' => 'LT', '371' => 'LV',
        '372' => 'EE', '373' => 'MD', '374' => 'AM', '375' => 'BY',
        '376' => 'AD', '377' => 'MC', '378' => 'SM', '380' => 'UA',
        '381' => 'RS', '382' => 'ME', '385' => 'HR', '386' => 'SI',
        '387' => 'BA', '389' => 'MK', '420' => 'CZ', '421' => 'SK',
        '423' => 'LI', '500' => 'FK', '501' => 'BZ', '502' => 'GT',
        '503' => 'SV', '504' => 'HN', '505' => 'NI', '506' => 'CR',
        '507' => 'PA', '508' => 'PM', '509' => 'HT', '590' => 'GP',
        '591' => 'BO', '592' => 'GY', '593' => 'EC', '594' => 'GF',
        '595' => 'PY', '596' => 'MQ', '597' => 'SR', '598' => 'UY',
        '599' => 'AN', '670' => 'TL', '672' => 'NF', '673' => 'BN',
        '674' => 'NR', '675' => 'PG', '676' => 'TO', '677' => 'SB',
        '678' => 'VU', '679' => 'FJ', '680' => 'PW', '681' => 'WF',
        '682' => 'CK', '683' => 'NU', '685' => 'WS', '686' => 'KI',
        '687' => 'NC', '688' => 'TV', '689' => 'PF', '690' => 'TK',
        '691' => 'FM', '692' => 'MH', '850' => 'KP', '852' => 'HK',
        '853' => 'MO', '855' => 'KH', '856' => 'LA', '880' => 'BD',
        '886' => 'TW', '960' => 'MV', '961' => 'LB', '962' => 'JO',
        '963' => 'SY', '964' => 'IQ', '965' => 'KW', '966' => 'SA',
        '967' => 'YE', '968' => 'OM', '970' => 'PS', '971' => 'AE',
        '972' => 'IL', '973' => 'BH', '974' => 'QA', '975' => 'BT',
        '976' => 'MN', '977' => 'NP', '992' => 'TJ', '993' => 'TM',
        '994' => 'AZ', '995' => 'GE', '996' => 'KG', '998' => 'UZ',

        // ── NANP overrides (1-xxx) ─────────────────────────────────
        '1242' => 'BS', '1246' => 'BB', '1264' => 'AI', '1268' => 'AG',
        '1284' => 'VG', '1340' => 'VI', '1345' => 'KY', '1441' => 'BM',
        '1473' => 'GD', '1649' => 'TC', '1664' => 'MS', '1670' => 'MP',
        '1671' => 'GU', '1684' => 'AS', '1721' => 'SX', '1758' => 'LC',
        '1767' => 'DM', '1784' => 'VC', '1787' => 'PR', '1809' => 'DO',
        '1868' => 'TT', '1869' => 'KN', '1876' => 'JM', '1939' => 'PR',
    ];

    // ── Singleton support ────────────────────────────────────────────────
    private static ?self $instance = null;

    public static function getInstance(): static
    {
        return static::$instance ??= new static();
    }

    // ════════════════════════════════════════════════════════════════════
    // PUBLIC API
    // ════════════════════════════════════════════════════════════════════

    /**
     * Resolve an ISO 3166-1 alpha-2 country code from a phone number.
     *
     * @param string      $phone            Raw phone number (any format).
     * @param string|null $localCountryCode Fallback used when the number
     *                                       starts with 0 or cannot be resolved.
     * @return string ISO alpha-2 code, or 'XX' when unresolvable.
     */
    public static function resolveCountryCode(string $phone, ?string $localCountryCode = null): string
    {
        [$code] = static::getInstance()->doResolve($phone, $localCountryCode);
        return $code;
    }

    /**
     * Analyse a phone number and return a rich PhoneAnalysis object.
     *
     * Usage:
     *   $r = PhoneCountryService::analyze('+212612345678');
     *   echo $r->countryName;   // Morocco
     *   echo $r->toHtml();      // self-contained HTML card
     *
     * @param string      $phone            Raw phone number.
     * @param string|null $localCountryCode Fallback country code for local numbers.
     */
    public static function analyze(string $phone, ?string $localCountryCode = null): PhoneAnalysis
    {
        return static::getInstance()->buildResult($phone, $localCountryCode);
    }

    // ════════════════════════════════════════════════════════════════════
    // INTERNALS
    // ════════════════════════════════════════════════════════════════════

    /**
     * Core resolution — returns [isoCode, dialingCode, normalised, inputFormat, usedFallback].
     *
     * @return array{0:string,1:string,2:string,3:string,4:bool}
     */
    protected function doResolve(string $phone, ?string $localCountryCode): array
    {
        // 1. Strip everything except digits and a leading +
        $stripped = preg_replace('/[^\d+]/', '', $phone) ?? '';

        $inputFormat  = 'UNKNOWN';
        $usedFallback = false;

        // 2. Classify input format and normalise to digits-only
        if (str_starts_with($stripped, '+')) {
            $normalised  = ltrim($stripped, '+');
            $inputFormat = 'E164';
        } elseif (str_starts_with($stripped, '00')) {
            $normalised  = substr($stripped, 2);
            $inputFormat = 'DOUBLE_ZERO';
        } elseif (str_starts_with($stripped, '0')) {
            // Local format – use fallback immediately
            $normalised   = ltrim($stripped, '0');
            $inputFormat  = 'LOCAL';
            $usedFallback = ($localCountryCode !== null);
            if ($localCountryCode !== null) {
                $dialingCode = $this->dialingCodeForIso($localCountryCode);
                return [$localCountryCode, $dialingCode, $normalised, $inputFormat, true];
            }
            return ['XX', '0', $normalised, $inputFormat, false];
        } else {
            $normalised  = $stripped;
            $inputFormat = 'NUMERIC';
        }

        // 3. Longest-prefix match (try 4 → 3 → 2 → 1 digits)
        foreach ([4, 3, 2, 1] as $len) {
            $prefix = substr($normalised, 0, $len);
            if (isset(static::$prefixMap[$prefix])) {
                $iso         = static::$prefixMap[$prefix];
                $dialingCode = $prefix;
                // Handle fallback override for numeric input
                if ($iso === 'XX' && $localCountryCode !== null) {
                    $usedFallback = true;
                    $iso          = $localCountryCode;
                    $dialingCode  = $this->dialingCodeForIso($iso);
                }
                return [$iso, $dialingCode, $normalised, $inputFormat, $usedFallback];
            }
        }

        // 4. Unresolvable
        if ($localCountryCode !== null) {
            $usedFallback = true;
            $dialingCode  = $this->dialingCodeForIso($localCountryCode);
            return [$localCountryCode, $dialingCode, $normalised, $inputFormat, true];
        }

        return ['XX', '0', $normalised, $inputFormat, false];
    }

    /** Build the full PhoneAnalysis. */
    protected function buildResult(string $phone, ?string $localCountryCode): PhoneAnalysis
    {
        [$iso, $dialingCode, $normalised, $inputFormat, $usedFallback] = $this->doResolve($phone, $localCountryCode);

        $meta = CountryMetadata::get($iso);
        [$countryName, $flag, $region, $continent, $capital, $currency, $currencyName, $language, $timezone] = $meta;

        // National number = digits after the dialing code
        $nationalNumber = strlen($dialingCode) > 0 && str_starts_with($normalised, $dialingCode)
            ? substr($normalised, strlen($dialingCode))
            : $normalised;

        $e164 = ($iso !== 'XX') ? '+' . $dialingCode . $nationalNumber : $normalised;

        $digitCount = strlen($nationalNumber);
        $isValid    = $this->isValidLength($iso, $digitCount);
        $isPossible = $this->isPossibleLength($iso, $digitCount);
        $numberType = $this->guessNumberType($iso, $nationalNumber);

        return new PhoneAnalysis(
            raw:                  $phone,
            normalized:           $normalised,
            e164:                 $e164,
            nationalNumber:       $nationalNumber,
            dialingCode:          $dialingCode,
            countryCode:          $iso,
            countryName:          $countryName,
            flag:                 $flag,
            region:               $region,
            continent:            $continent,
            capital:              $capital,
            currency:             $currency,
            currencyName:         $currencyName,
            language:             $language,
            timezone:             $timezone,
            numberType:           $numberType,
            isValid:              $isValid,
            isPossible:           $isPossible,
            digitCount:           $digitCount,
            formatNational:       $this->formatNational($iso, $nationalNumber),
            formatInternational:  $this->formatInternational($dialingCode, $nationalNumber),
            inputFormat:          $inputFormat,
            usedFallback:         $usedFallback,
            resolvedAt:           (new \DateTimeImmutable('now', new \DateTimeZone('UTC')))->format(\DateTimeInterface::ATOM),
        );
    }

    // ── Helpers ──────────────────────────────────────────────────────────

    /** Reverse-lookup dialing code for a given ISO code. */
    protected function dialingCodeForIso(string $iso): string
    {
        foreach (static::$prefixMap as $prefix => $code) {
            if ($code === $iso && strlen((string)$prefix) <= 3) {
                return (string)$prefix;
            }
        }
        return '0';
    }

    /**
     * Basic validity: national number digit count in expected range.
     * Uses a conservative country-length table; defaults to 6–12.
     */
    protected function isValidLength(string $iso, int $len): bool
    {
        [$min, $max] = $this->lengthRange($iso);
        return $len >= $min && $len <= $max;
    }

    protected function isPossibleLength(string $iso, int $len): bool
    {
        [$min, $max] = $this->lengthRange($iso);
        return $len >= ($min - 1) && $len <= ($max + 1);
    }

    /** [min, max] digits for the national number in each country. */
    private function lengthRange(string $iso): array
    {
        return match ($iso) {
            'MA', 'DZ', 'TN', 'SN', 'CI' => [9, 9],
            'FR', 'BE', 'CH', 'DE', 'AT',
            'ES', 'IT', 'PT', 'NL', 'SE',
            'NO', 'DK', 'FI', 'GB', 'IE' => [9, 10],
            'US', 'CA'                   => [10, 10],
            'CN', 'IN', 'BR'             => [10, 11],
            'NG', 'GH', 'KE', 'ZA', 'EG' => [9, 10],
            default                      => [6, 12],
        };
    }

    /**
     * Heuristic number type detection.
     * Returns MOBILE, FIXED_LINE, TOLL_FREE, PREMIUM, or UNKNOWN.
     */
    protected function guessNumberType(string $iso, string $national): string
    {
        if (empty($national)) {
            return 'UNKNOWN';
        }

        $first  = $national[0];
        $prefix = substr($national, 0, 2);

        // Toll-free / premium (universal)
        if (in_array(substr($national, 0, 3), ['800', '900', '808', '844', '855', '866', '877', '888'], true)) {
            return str_starts_with($national, '900') ? 'PREMIUM' : 'TOLL_FREE';
        }

        return match ($iso) {
            'MA' => in_array($first, ['6', '7']) ? 'MOBILE' : 'FIXED_LINE',
            'DZ' => in_array($first, ['5', '6', '7']) ? 'MOBILE' : 'FIXED_LINE',
            'TN' => in_array($first, ['2', '4', '5', '9']) ? 'MOBILE' : 'FIXED_LINE',
            'FR' => $first === '6' || $first === '7' ? 'MOBILE' : 'FIXED_LINE',
            'GB' => str_starts_with($national, '7') ? 'MOBILE' : 'FIXED_LINE',
            'DE' => in_array($first, ['1', '15', '16', '17'][0] ?? '', ['1']) ? 'MOBILE' : 'FIXED_LINE',
            'US', 'CA' => 'FIXED_LINE',  // cannot distinguish without area-code DB
            'NG' => in_array($prefix, ['07', '08', '09']) ? 'MOBILE' : 'FIXED_LINE',
            'ZA' => in_array($first, ['6', '7', '8']) ? 'MOBILE' : 'FIXED_LINE',
            'EG' => in_array($prefix, ['10', '11', '12', '15']) ? 'MOBILE' : 'FIXED_LINE',
            'IN' => in_array($first, ['6', '7', '8', '9']) ? 'MOBILE' : 'FIXED_LINE',
            'CN' => in_array($first, ['1']) ? 'MOBILE' : 'FIXED_LINE',
            default => 'UNKNOWN',
        };
    }

    /** Simple national formatter (groups of digits). */
    protected function formatNational(string $iso, string $national): string
    {
        if (empty($national)) {
            return $national;
        }

        // Group into blocks of 2 for most countries (common European/African pattern)
        $chunks = str_split($national, 2);
        return implode(' ', $chunks);
    }

    protected function formatInternational(string $dialingCode, string $national): string
    {
        if (empty($national)) {
            return '+' . $dialingCode;
        }

        $chunks = str_split($national, 3);
        return '+' . $dialingCode . ' ' . implode(' ', $chunks);
    }
}

