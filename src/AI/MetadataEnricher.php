<?php

namespace Wal3fo\PhoneCountry\AI;

/**
 * Enriches a resolved country code with:
 *   - Primary timezone (IANA identifier)
 *   - ISO 4217 currency code
 *   - BCP 47 primary language tag
 *   - Expected subscriber number length
 *   - Whether the number's subscriber portion has the correct length
 */
class MetadataEnricher
{
    /**
     * Per-country metadata.
     * Format: [timezone, currency, language, subscriber_length]
     *
     * subscriber_length = digits after the dial code (e.g. Morocco +212 → 9 digits)
     * null = variable or unknown
     */
    private static array $meta = [
        'AF' => ['Asia/Kabul',              'AFN', 'fa',  9],
        'AL' => ['Europe/Tirane',           'ALL', 'sq',  9],
        'DZ' => ['Africa/Algiers',          'DZD', 'ar',  9],
        'AD' => ['Europe/Andorra',          'EUR', 'ca',  6],
        'AO' => ['Africa/Luanda',           'AOA', 'pt',  9],
        'AG' => ['America/Antigua',         'XCD', 'en',  7],
        'AR' => ['America/Argentina/Buenos_Aires', 'ARS', 'es', 10],
        'AM' => ['Asia/Yerevan',            'AMD', 'hy',  8],
        'AU' => ['Australia/Sydney',        'AUD', 'en',  9],
        'AT' => ['Europe/Vienna',           'EUR', 'de', 10],
        'AZ' => ['Asia/Baku',              'AZN', 'az',  9],
        'BS' => ['America/Nassau',          'BSD', 'en',  7],
        'BH' => ['Asia/Bahrain',            'BHD', 'ar',  8],
        'BD' => ['Asia/Dhaka',              'BDT', 'bn', 10],
        'BB' => ['America/Barbados',        'BBD', 'en',  7],
        'BY' => ['Europe/Minsk',            'BYN', 'be',  9],
        'BE' => ['Europe/Brussels',         'EUR', 'nl',  9],
        'BZ' => ['America/Belize',          'BZD', 'en',  7],
        'BJ' => ['Africa/Porto-Novo',       'XOF', 'fr',  8],
        'BT' => ['Asia/Thimphu',            'BTN', 'dz',  8],
        'BO' => ['America/La_Paz',          'BOB', 'es',  8],
        'BA' => ['Europe/Sarajevo',         'BAM', 'bs',  8],
        'BW' => ['Africa/Gaborone',         'BWP', 'en',  8],
        'BR' => ['America/Sao_Paulo',       'BRL', 'pt', 11],
        'BN' => ['Asia/Brunei',             'BND', 'ms',  7],
        'BG' => ['Europe/Sofia',            'BGN', 'bg',  9],
        'BF' => ['Africa/Ouagadougou',      'XOF', 'fr',  8],
        'BI' => ['Africa/Bujumbura',        'BIF', 'fr',  8],
        'CV' => ['Atlantic/Cape_Verde',     'CVE', 'pt',  7],
        'KH' => ['Asia/Phnom_Penh',         'KHR',  'km', 9],
        'CM' => ['Africa/Douala',           'XAF', 'fr',  9],
        'CA' => ['America/Toronto',         'CAD', 'en', 10],
        'CF' => ['Africa/Bangui',           'XAF', 'fr',  8],
        'TD' => ['Africa/Ndjamena',         'XAF', 'fr',  8],
        'CL' => ['America/Santiago',        'CLP', 'es',  9],
        'CN' => ['Asia/Shanghai',           'CNY', 'zh', 11],
        'CO' => ['America/Bogota',          'COP', 'es', 10],
        'KM' => ['Indian/Comoro',           'KMF', 'ar',  7],
        'CG' => ['Africa/Brazzaville',      'XAF', 'fr',  9],
        'CD' => ['Africa/Kinshasa',         'CDF', 'fr',  9],
        'CR' => ['America/Costa_Rica',      'CRC', 'es',  8],
        'HR' => ['Europe/Zagreb',           'EUR', 'hr',  9],
        'CU' => ['America/Havana',          'CUP', 'es',  8],
        'CY' => ['Asia/Nicosia',            'EUR', 'el',  8],
        'CZ' => ['Europe/Prague',           'CZK', 'cs',  9],
        'DK' => ['Europe/Copenhagen',       'DKK', 'da',  8],
        'DJ' => ['Africa/Djibouti',         'DJF', 'fr',  8],
        'DM' => ['America/Dominica',        'XCD', 'en',  7],
        'DO' => ['America/Santo_Domingo',   'DOP', 'es', 10],
        'EC' => ['America/Guayaquil',       'USD', 'es', 10],
        'EG' => ['Africa/Cairo',            'EGP', 'ar', 10],
        'SV' => ['America/El_Salvador',     'USD', 'es',  8],
        'GQ' => ['Africa/Malabo',           'XAF', 'es',  9],
        'ER' => ['Africa/Asmara',           'ERN', 'ti',  7],
        'EE' => ['Europe/Tallinn',          'EUR', 'et',  8],
        'ET' => ['Africa/Addis_Ababa',      'ETB', 'am',  9],
        'FJ' => ['Pacific/Fiji',            'FJD', 'en',  7],
        'FI' => ['Europe/Helsinki',         'EUR', 'fi', 10],
        'FR' => ['Europe/Paris',            'EUR', 'fr',  9],
        'GA' => ['Africa/Libreville',       'XAF', 'fr',  8],
        'GM' => ['Africa/Banjul',           'GMD', 'en',  7],
        'GE' => ['Asia/Tbilisi',            'GEL', 'ka',  9],
        'DE' => ['Europe/Berlin',           'EUR', 'de', 10],
        'GH' => ['Africa/Accra',            'GHS', 'en',  9],
        'GR' => ['Europe/Athens',           'EUR', 'el', 10],
        'GD' => ['America/Grenada',         'XCD', 'en',  7],
        'GT' => ['America/Guatemala',       'GTQ', 'es',  8],
        'GN' => ['Africa/Conakry',          'GNF', 'fr',  9],
        'GW' => ['Africa/Bissau',           'XOF', 'pt',  7],
        'GY' => ['America/Guyana',          'GYD', 'en',  7],
        'HT' => ['America/Port-au-Prince',  'HTG', 'fr',  8],
        'HN' => ['America/Tegucigalpa',     'HNL', 'es',  8],
        'HK' => ['Asia/Hong_Kong',          'HKD', 'zh',  8],
        'HU' => ['Europe/Budapest',         'HUF', 'hu',  9],
        'IS' => ['Atlantic/Reykjavik',      'ISK', 'is',  7],
        'IN' => ['Asia/Kolkata',            'INR', 'hi', 10],
        'ID' => ['Asia/Jakarta',            'IDR', 'id', 10],
        'IR' => ['Asia/Tehran',             'IRR', 'fa', 10],
        'IQ' => ['Asia/Baghdad',            'IQD', 'ar', 10],
        'IE' => ['Europe/Dublin',           'EUR', 'en',  9],
        'IL' => ['Asia/Jerusalem',          'ILS', 'he',  9],
        'IT' => ['Europe/Rome',             'EUR', 'it', 10],
        'JM' => ['America/Jamaica',         'JMD', 'en',  7],
        'JP' => ['Asia/Tokyo',              'JPY', 'ja', 10],
        'JO' => ['Asia/Amman',              'JOD', 'ar',  9],
        'KZ' => ['Asia/Almaty',             'KZT', 'kk', 10],
        'KE' => ['Africa/Nairobi',          'KES', 'sw',  9],
        'KI' => ['Pacific/Tarawa',          'AUD', 'en',  8],
        'KP' => ['Asia/Pyongyang',          'KPW', 'ko',  8],
        'KR' => ['Asia/Seoul',              'KRW', 'ko', 10],
        'KW' => ['Asia/Kuwait',             'KWD', 'ar',  8],
        'KG' => ['Asia/Bishkek',            'KGS', 'ky',  9],
        'LA' => ['Asia/Vientiane',          'LAK', 'lo',  9],
        'LV' => ['Europe/Riga',             'EUR', 'lv',  8],
        'LB' => ['Asia/Beirut',             'LBP', 'ar',  8],
        'LS' => ['Africa/Maseru',           'LSL', 'st',  8],
        'LR' => ['Africa/Monrovia',         'LRD', 'en',  8],
        'LY' => ['Africa/Tripoli',          'LYD', 'ar',  9],
        'LI' => ['Europe/Vaduz',            'CHF', 'de',  7],
        'LT' => ['Europe/Vilnius',          'EUR', 'lt',  8],
        'LU' => ['Europe/Luxembourg',       'EUR', 'lb',  9],
        'MO' => ['Asia/Macau',              'MOP', 'zh',  8],
        'MG' => ['Indian/Antananarivo',     'MGA', 'mg',  9],
        'MW' => ['Africa/Blantyre',         'MWK', 'ny',  9],
        'MY' => ['Asia/Kuala_Lumpur',       'MYR', 'ms',  9],
        'MV' => ['Indian/Maldives',         'MVR', 'dv',  7],
        'ML' => ['Africa/Bamako',           'XOF', 'fr',  8],
        'MT' => ['Europe/Malta',            'EUR', 'mt',  8],
        'MH' => ['Pacific/Majuro',          'USD', 'mh',  7],
        'MQ' => ['America/Martinique',      'EUR', 'fr',  9],
        'MR' => ['Africa/Nouakchott',       'MRU', 'ar',  8],
        'MU' => ['Indian/Mauritius',        'MUR', 'mfe', 8],
        'MX' => ['America/Mexico_City',     'MXN', 'es', 10],
        'FM' => ['Pacific/Pohnpei',         'USD', 'en',  7],
        'MD' => ['Europe/Chisinau',         'MDL', 'ro',  8],
        'MC' => ['Europe/Monaco',           'EUR', 'fr',  8],
        'MN' => ['Asia/Ulaanbaatar',        'MNT', 'mn',  8],
        'ME' => ['Europe/Podgorica',        'EUR', 'sr',  8],
        'MA' => ['Africa/Casablanca',       'MAD', 'ar',  9],
        'MZ' => ['Africa/Maputo',           'MZN', 'pt',  9],
        'MM' => ['Asia/Rangoon',            'MMK', 'my',  9],
        'NA' => ['Africa/Windhoek',         'NAD', 'en',  9],
        'NR' => ['Pacific/Nauru',           'AUD', 'na',  7],
        'NP' => ['Asia/Kathmandu',          'NPR', 'ne', 10],
        'NL' => ['Europe/Amsterdam',        'EUR', 'nl',  9],
        'NZ' => ['Pacific/Auckland',        'NZD', 'en',  9],
        'NI' => ['America/Managua',         'NIO', 'es',  8],
        'NE' => ['Africa/Niamey',           'XOF', 'fr',  8],
        'NG' => ['Africa/Lagos',            'NGN', 'en', 10],
        'NO' => ['Europe/Oslo',             'NOK', 'no',  8],
        'OM' => ['Asia/Muscat',             'OMR', 'ar',  8],
        'PK' => ['Asia/Karachi',            'PKR', 'ur', 10],
        'PW' => ['Pacific/Palau',           'USD', 'pau', 7],
        'PA' => ['America/Panama',          'PAB', 'es',  8],
        'PG' => ['Pacific/Port_Moresby',    'PGK', 'tpi', 8],
        'PY' => ['America/Asuncion',        'PYG', 'es',  9],
        'PE' => ['America/Lima',            'PEN', 'es',  9],
        'PH' => ['Asia/Manila',             'PHP', 'tl', 10],
        'PL' => ['Europe/Warsaw',           'PLN', 'pl',  9],
        'PT' => ['Europe/Lisbon',           'EUR', 'pt',  9],
        'QA' => ['Asia/Qatar',              'QAR', 'ar',  8],
        'RO' => ['Europe/Bucharest',        'RON', 'ro', 10],
        'RU' => ['Europe/Moscow',           'RUB', 'ru', 10],
        'RW' => ['Africa/Kigali',           'RWF', 'rw',  9],
        'KN' => ['America/St_Kitts',        'XCD', 'en',  7],
        'LC' => ['America/St_Lucia',        'XCD', 'en',  7],
        'VC' => ['America/St_Vincent',      'XCD', 'en',  7],
        'WS' => ['Pacific/Apia',            'WST', 'sm',  7],
        'SM' => ['Europe/San_Marino',       'EUR', 'it', 10],
        'ST' => ['Africa/Sao_Tome',         'STN', 'pt',  7],
        'SA' => ['Asia/Riyadh',             'SAR', 'ar',  9],
        'SN' => ['Africa/Dakar',            'XOF', 'fr',  9],
        'RS' => ['Europe/Belgrade',         'RSD', 'sr',  9],
        'SC' => ['Indian/Mahe',             'SCR', 'fr',  7],
        'SL' => ['Africa/Freetown',         'SLL', 'en',  8],
        'SG' => ['Asia/Singapore',          'SGD', 'en',  8],
        'SK' => ['Europe/Bratislava',       'EUR', 'sk',  9],
        'SI' => ['Europe/Ljubljana',        'EUR', 'sl',  8],
        'SB' => ['Pacific/Guadalcanal',     'SBD', 'en',  7],
        'SO' => ['Africa/Mogadishu',        'SOS', 'so',  8],
        'ZA' => ['Africa/Johannesburg',     'ZAR', 'af',  9],
        'SS' => ['Africa/Juba',             'SSP', 'en',  9],
        'ES' => ['Europe/Madrid',           'EUR', 'es',  9],
        'LK' => ['Asia/Colombo',            'LKR', 'si',  9],
        'SD' => ['Africa/Khartoum',         'SDG', 'ar',  9],
        'SR' => ['America/Paramaribo',      'SRD', 'nl',  7],
        'SE' => ['Europe/Stockholm',        'SEK', 'sv',  9],
        'CH' => ['Europe/Zurich',           'CHF', 'de',  9],
        'SY' => ['Asia/Damascus',           'SYP', 'ar',  9],
        'TW' => ['Asia/Taipei',             'TWD', 'zh',  9],
        'TJ' => ['Asia/Dushanbe',           'TJS', 'tg',  9],
        'TZ' => ['Africa/Dar_es_Salaam',    'TZS', 'sw',  9],
        'TH' => ['Asia/Bangkok',            'THB', 'th',  9],
        'TL' => ['Asia/Dili',               'USD', 'pt',  8],
        'TG' => ['Africa/Lome',             'XOF', 'fr',  8],
        'TO' => ['Pacific/Tongatapu',       'TOP', 'to',  7],
        'TT' => ['America/Port_of_Spain',   'TTD', 'en',  7],
        'TN' => ['Africa/Tunis',            'TND', 'ar',  8],
        'TR' => ['Europe/Istanbul',         'TRY', 'tr', 10],
        'TM' => ['Asia/Ashgabat',           'TMT', 'tk',  8],
        'TV' => ['Pacific/Funafuti',        'AUD', 'tvl', 6],
        'UG' => ['Africa/Kampala',          'UGX', 'sw',  9],
        'UA' => ['Europe/Kiev',             'UAH', 'uk',  9],
        'AE' => ['Asia/Dubai',              'AED', 'ar',  9],
        'GB' => ['Europe/London',           'GBP', 'en', 10],
        'US' => ['America/New_York',        'USD', 'en', 10],
        'UY' => ['America/Montevideo',      'UYU', 'es',  9],
        'UZ' => ['Asia/Tashkent',           'UZS', 'uz',  9],
        'VU' => ['Pacific/Efate',           'VUV', 'bi',  7],
        'VE' => ['America/Caracas',         'VES', 'es', 10],
        'VN' => ['Asia/Ho_Chi_Minh',        'VND', 'vi', 10],
        'YE' => ['Asia/Aden',               'YER', 'ar',  9],
        'ZM' => ['Africa/Lusaka',           'ZMW', 'en',  9],
        'ZW' => ['Africa/Harare',           'ZWL', 'sn',  9],
    ];

    /**
     * Enrich a country code with metadata.
     *
     * @param  string  $countryCode  ISO 3166-1 alpha-2
     * @param  string  $cleanNumber  Digits-only number for length validation
     * @param  string  $dialCode     E.g. '+212'
     * @return array{timezone: ?string, currency: ?string, language: ?string, subscriber_length: ?int, length_valid: bool}
     */
    public static function enrich(string $countryCode, string $cleanNumber, string $dialCode): array
    {
        $data = self::$meta[$countryCode] ?? null;

        if ($data === null) {
            return [
                'timezone'          => null,
                'currency'          => null,
                'language'          => null,
                'subscriber_length' => null,
                'length_valid'      => true, // can't validate what we don't know
            ];
        }

        [$timezone, $currency, $language, $expectedLength] = $data;

        // Calculate actual subscriber length
        $dialDigits      = preg_replace('/[^0-9]/', '', $dialCode);
        $subscriberPart  = ltrim(substr($cleanNumber, strlen($dialDigits)), '0');

        // Allow ±1 digit tolerance for optional trunk prefixes
        $actualLength  = strlen(preg_replace('/[^0-9]/', '', $cleanNumber) ?: '');
        $dialLength    = strlen($dialDigits);
        $subLength     = $actualLength - $dialLength;

        $lengthValid = $subLength <= 0
            ? true // can't determine — don't flag
            : abs($subLength - $expectedLength) <= 1;

        return [
            'timezone'          => $timezone,
            'currency'          => $currency,
            'language'          => $language,
            'subscriber_length' => $expectedLength,
            'length_valid'      => $lengthValid,
        ];
    }
}
