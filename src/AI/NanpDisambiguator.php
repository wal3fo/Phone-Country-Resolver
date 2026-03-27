<?php

namespace Wal3fo\PhoneCountry\AI;

/**
 * Disambiguates phone numbers that share the +1 North American Numbering Plan (NANP) prefix.
 *
 * The +1 prefix is shared by 25+ countries and territories:
 * USA, Canada, Jamaica, Bahamas, Barbados, Trinidad & Tobago, etc.
 *
 * Resolution strategy:
 *   1. Extract the 3-digit area code following +1.
 *   2. Look it up in the NANP area-code → country map.
 *   3. Fall back to 'US' (the most statistically common) if unknown.
 *
 * This map covers all assigned NANP area codes as of 2024.
 */
class NanpDisambiguator
{
    /**
     * Area codes assigned to non-US NANP members.
     * US area codes are not listed — anything not in this table defaults to US.
     *
     * Source: NANPA (nanpa.com) + ITU
     */
    private static array $nonUsAreaCodes = [
        // Canada
        '204' => 'CA', '226' => 'CA', '236' => 'CA', '249' => 'CA',
        '250' => 'CA', '263' => 'CA', '289' => 'CA', '306' => 'CA',
        '343' => 'CA', '354' => 'CA', '365' => 'CA', '367' => 'CA',
        '368' => 'CA', '382' => 'CA', '387' => 'CA', '403' => 'CA',
        '416' => 'CA', '418' => 'CA', '428' => 'CA', '431' => 'CA',
        '437' => 'CA', '438' => 'CA', '450' => 'CA', '468' => 'CA',
        '474' => 'CA', '506' => 'CA', '514' => 'CA', '519' => 'CA',
        '548' => 'CA', '579' => 'CA', '581' => 'CA', '584' => 'CA',
        '587' => 'CA', '604' => 'CA', '613' => 'CA', '639' => 'CA',
        '647' => 'CA', '672' => 'CA', '683' => 'CA', '705' => 'CA',
        '709' => 'CA', '742' => 'CA', '753' => 'CA', '778' => 'CA',
        '780' => 'CA', '782' => 'CA', '807' => 'CA', '819' => 'CA',
        '825' => 'CA', '867' => 'CA', '873' => 'CA', '879' => 'CA',
        '902' => 'CA', '905' => 'CA',

        // Jamaica
        '658' => 'JM', '876' => 'JM',

        // Bahamas
        '242' => 'BS',

        // Barbados
        '246' => 'BB',

        // Antigua and Barbuda
        '268' => 'AG',

        // Anguilla
        '264' => 'AI',

        // US Virgin Islands
        '340' => 'VI',

        // Cayman Islands
        '345' => 'KY',

        // Bermuda
        '441' => 'BM',

        // Grenada
        '473' => 'GD',

        // Turks and Caicos
        '649' => 'TC',

        // Montserrat
        '664' => 'MS',

        // Guam
        '671' => 'GU',

        // Northern Mariana Islands
        '670' => 'MP',

        // American Samoa
        '684' => 'AS',

        // Sint Maarten
        '721' => 'SX',

        // Saint Lucia
        '758' => 'LC',

        // Dominica
        '767' => 'DM',

        // Puerto Rico
        '787' => 'PR', '939' => 'PR',

        // Saint Vincent and the Grenadines
        '784' => 'VC',

        // Dominican Republic
        '809' => 'DO', '829' => 'DO', '849' => 'DO',

        // Trinidad and Tobago
        '868' => 'TT',

        // Saint Kitts and Nevis
        '869' => 'KN',

        // Haiti
        // (Haiti left NANP; +509 is their code now, not +1)

        // US Virgin Islands (extra)
        '284' => 'VG', // British Virgin Islands
    ];

    /**
     * Attempt to disambiguate a +1 number.
     *
     * @param  string  $cleanNumber  Digits only, e.g. "18765551234"
     * @return array{code: string, note: string|null}
     */
    public static function disambiguate(string $cleanNumber): array
    {
        // Strip the leading 1 if present
        $digits = ltrim($cleanNumber, '1');

        // Need at least 10 digits total after country code
        if (strlen($digits) < 10) {
            return ['code' => 'US', 'note' => 'Insufficient digits to disambiguate NANP number; defaulting to US.'];
        }

        $areaCode = substr($digits, 0, 3);

        if (isset(self::$nonUsAreaCodes[$areaCode])) {
            $country = self::$nonUsAreaCodes[$areaCode];
            return [
                'code' => $country,
                'note' => "Area code {$areaCode} is assigned to {$country}, not US.",
            ];
        }

        return [
            'code' => 'US',
            'note' => null, // No note needed — US is unambiguous majority
        ];
    }

    /**
     * Returns true if this clean number looks like a NANP (+1) number.
     */
    public static function isNanp(string $cleanNumber): bool
    {
        return str_starts_with($cleanNumber, '1') && strlen($cleanNumber) >= 11;
    }
}
