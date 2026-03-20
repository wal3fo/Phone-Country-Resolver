<?php

namespace Wal3fo\PhoneCountry;

class PhoneCountryService
{
    /**
     * Country metadata: [countryCode => [name, dialCode]]
     */
    private static array $countryMeta = [
        'AF' => ['Afghanistan',                    '+93'],
        'AL' => ['Albania',                        '+355'],
        'DZ' => ['Algeria',                        '+213'],
        'AC' => ['Ascension Island',               '+247'],
        'AD' => ['Andorra',                        '+376'],
        'AE' => ['United Arab Emirates',           '+971'],
        'AG' => ['Antigua and Barbuda',            '+1268'],
        'AI' => ['Anguilla',                       '+1264'],
        'AM' => ['Armenia',                        '+374'],
        'AO' => ['Angola',                         '+244'],
        'AR' => ['Argentina',                      '+54'],
        'AS' => ['American Samoa',                 '+1684'],
        'AT' => ['Austria',                        '+43'],
        'AU' => ['Australia',                      '+61'],
        'AW' => ['Aruba',                          '+297'],
        'AZ' => ['Azerbaijan',                     '+994'],
        'BA' => ['Bosnia and Herzegovina',         '+387'],
        'BB' => ['Barbados',                       '+1246'],
        'BD' => ['Bangladesh',                     '+880'],
        'BE' => ['Belgium',                        '+32'],
        'BF' => ['Burkina Faso',                   '+226'],
        'BG' => ['Bulgaria',                       '+359'],
        'BH' => ['Bahrain',                        '+973'],
        'BI' => ['Burundi',                        '+257'],
        'BJ' => ['Benin',                          '+229'],
        'BM' => ['Bermuda',                        '+1441'],
        'BN' => ['Brunei',                         '+673'],
        'BO' => ['Bolivia',                        '+591'],
        'BR' => ['Brazil',                         '+55'],
        'BS' => ['Bahamas',                        '+1242'],
        'BT' => ['Bhutan',                         '+975'],
        'BW' => ['Botswana',                       '+267'],
        'BY' => ['Belarus',                        '+375'],
        'BZ' => ['Belize',                         '+501'],
        'CA' => ['Canada',                         '+1'],
        'CD' => ['DR Congo',                       '+243'],
        'CF' => ['Central African Republic',       '+236'],
        'CG' => ['Republic of the Congo',          '+242'],
        'CH' => ['Switzerland',                    '+41'],
        'CI' => ["Côte d'Ivoire",                  '+225'],
        'CK' => ['Cook Islands',                   '+682'],
        'CL' => ['Chile',                          '+56'],
        'CM' => ['Cameroon',                       '+237'],
        'CN' => ['China',                          '+86'],
        'CO' => ['Colombia',                       '+57'],
        'CR' => ['Costa Rica',                     '+506'],
        'CU' => ['Cuba',                           '+53'],
        'CV' => ['Cape Verde',                     '+238'],
        'CW' => ['Curaçao',                        '+599'],
        'CY' => ['Cyprus',                         '+357'],
        'CZ' => ['Czech Republic',                 '+420'],
        'DE' => ['Germany',                        '+49'],
        'DJ' => ['Djibouti',                       '+253'],
        'DK' => ['Denmark',                        '+45'],
        'DM' => ['Dominica',                       '+1767'],
        'DO' => ['Dominican Republic',             '+1809'],
        'DZ' => ['Algeria',                        '+213'],
        'EC' => ['Ecuador',                        '+593'],
        'EE' => ['Estonia',                        '+372'],
        'EG' => ['Egypt',                          '+20'],
        'ER' => ['Eritrea',                        '+291'],
        'ES' => ['Spain',                          '+34'],
        'ET' => ['Ethiopia',                       '+251'],
        'FI' => ['Finland',                        '+358'],
        'FJ' => ['Fiji',                           '+679'],
        'FK' => ['Falkland Islands',               '+500'],
        'FM' => ['Micronesia',                     '+691'],
        'FO' => ['Faroe Islands',                  '+298'],
        'FR' => ['France',                         '+33'],
        'GA' => ['Gabon',                          '+241'],
        'GB' => ['United Kingdom',                 '+44'],
        'GD' => ['Grenada',                        '+1473'],
        'GE' => ['Georgia',                        '+995'],
        'GF' => ['French Guiana',                  '+594'],
        'GH' => ['Ghana',                          '+233'],
        'GI' => ['Gibraltar',                      '+350'],
        'GL' => ['Greenland',                      '+299'],
        'GM' => ['Gambia',                         '+220'],
        'GN' => ['Guinea',                         '+224'],
        'GP' => ['Guadeloupe',                     '+590'],
        'GQ' => ['Equatorial Guinea',              '+240'],
        'GR' => ['Greece',                         '+30'],
        'GT' => ['Guatemala',                      '+502'],
        'GU' => ['Guam',                           '+1671'],
        'GW' => ['Guinea-Bissau',                  '+245'],
        'GY' => ['Guyana',                         '+592'],
        'HK' => ['Hong Kong',                      '+852'],
        'HN' => ['Honduras',                       '+504'],
        'HR' => ['Croatia',                        '+385'],
        'HT' => ['Haiti',                          '+509'],
        'HU' => ['Hungary',                        '+36'],
        'ID' => ['Indonesia',                      '+62'],
        'IE' => ['Ireland',                        '+353'],
        'IL' => ['Israel',                         '+972'],
        'IN' => ['India',                          '+91'],
        'IO' => ['British Indian Ocean Territory', '+246'],
        'IQ' => ['Iraq',                           '+964'],
        'IR' => ['Iran',                           '+98'],
        'IS' => ['Iceland',                        '+354'],
        'IT' => ['Italy',                          '+39'],
        'JM' => ['Jamaica',                        '+1876'],
        'JO' => ['Jordan',                         '+962'],
        'JP' => ['Japan',                          '+81'],
        'KE' => ['Kenya',                          '+254'],
        'KG' => ['Kyrgyzstan',                     '+996'],
        'KH' => ['Cambodia',                       '+855'],
        'KI' => ['Kiribati',                       '+686'],
        'KM' => ['Comoros',                        '+269'],
        'KN' => ['Saint Kitts and Nevis',          '+1869'],
        'KP' => ['North Korea',                    '+850'],
        'KR' => ['South Korea',                    '+82'],
        'KW' => ['Kuwait',                         '+965'],
        'KY' => ['Cayman Islands',                 '+1345'],
        'KZ' => ['Kazakhstan',                     '+7'],
        'LA' => ['Laos',                           '+856'],
        'LB' => ['Lebanon',                        '+961'],
        'LC' => ['Saint Lucia',                    '+1758'],
        'LI' => ['Liechtenstein',                  '+423'],
        'LK' => ['Sri Lanka',                      '+94'],
        'LR' => ['Liberia',                        '+231'],
        'LS' => ['Lesotho',                        '+266'],
        'LT' => ['Lithuania',                      '+370'],
        'LU' => ['Luxembourg',                     '+352'],
        'LV' => ['Latvia',                         '+371'],
        'LY' => ['Libya',                          '+218'],
        'MA' => ['Morocco',                        '+212'],
        'MC' => ['Monaco',                         '+377'],
        'MD' => ['Moldova',                        '+373'],
        'ME' => ['Montenegro',                     '+382'],
        'MG' => ['Madagascar',                     '+261'],
        'MH' => ['Marshall Islands',               '+692'],
        'MK' => ['North Macedonia',                '+389'],
        'ML' => ['Mali',                           '+223'],
        'MM' => ['Myanmar',                        '+95'],
        'MN' => ['Mongolia',                       '+976'],
        'MO' => ['Macao',                          '+853'],
        'MP' => ['Northern Mariana Islands',       '+1670'],
        'MQ' => ['Martinique',                     '+596'],
        'MR' => ['Mauritania',                     '+222'],
        'MS' => ['Montserrat',                     '+1664'],
        'MT' => ['Malta',                          '+356'],
        'MU' => ['Mauritius',                      '+230'],
        'MV' => ['Maldives',                       '+960'],
        'MW' => ['Malawi',                         '+265'],
        'MX' => ['Mexico',                         '+52'],
        'MY' => ['Malaysia',                       '+60'],
        'MZ' => ['Mozambique',                     '+258'],
        'NA' => ['Namibia',                        '+264'],
        'NC' => ['New Caledonia',                  '+687'],
        'NE' => ['Niger',                          '+227'],
        'NF' => ['Norfolk Island',                 '+672'],
        'NG' => ['Nigeria',                        '+234'],
        'NI' => ['Nicaragua',                      '+505'],
        'NL' => ['Netherlands',                    '+31'],
        'NO' => ['Norway',                         '+47'],
        'NP' => ['Nepal',                          '+977'],
        'NR' => ['Nauru',                          '+674'],
        'NU' => ['Niue',                           '+683'],
        'NZ' => ['New Zealand',                    '+64'],
        'OM' => ['Oman',                           '+968'],
        'PA' => ['Panama',                         '+507'],
        'PE' => ['Peru',                           '+51'],
        'PF' => ['French Polynesia',               '+689'],
        'PG' => ['Papua New Guinea',               '+675'],
        'PH' => ['Philippines',                    '+63'],
        'PK' => ['Pakistan',                       '+92'],
        'PL' => ['Poland',                         '+48'],
        'PM' => ['Saint Pierre and Miquelon',      '+508'],
        'PR' => ['Puerto Rico',                    '+1787'],
        'PS' => ['Palestinian Territory',          '+970'],
        'PT' => ['Portugal',                       '+351'],
        'PW' => ['Palau',                          '+680'],
        'PY' => ['Paraguay',                       '+595'],
        'QA' => ['Qatar',                          '+974'],
        'RE' => ['Réunion',                        '+262'],
        'RO' => ['Romania',                        '+40'],
        'RS' => ['Serbia',                         '+381'],
        'RU' => ['Russia',                         '+7'],
        'RW' => ['Rwanda',                         '+250'],
        'SA' => ['Saudi Arabia',                   '+966'],
        'SB' => ['Solomon Islands',                '+677'],
        'SC' => ['Seychelles',                     '+248'],
        'SD' => ['Sudan',                          '+249'],
        'SE' => ['Sweden',                         '+46'],
        'SG' => ['Singapore',                      '+65'],
        'SH' => ['Saint Helena',                   '+290'],
        'SI' => ['Slovenia',                       '+386'],
        'SK' => ['Slovakia',                       '+421'],
        'SL' => ['Sierra Leone',                   '+232'],
        'SM' => ['San Marino',                     '+378'],
        'SN' => ['Senegal',                        '+221'],
        'SO' => ['Somalia',                        '+252'],
        'SR' => ['Suriname',                       '+597'],
        'SS' => ['South Sudan',                    '+211'],
        'ST' => ['São Tomé and Príncipe',          '+239'],
        'SV' => ['El Salvador',                    '+503'],
        'SX' => ['Sint Maarten',                   '+1721'],
        'SY' => ['Syria',                          '+963'],
        'SZ' => ['Eswatini',                       '+268'],
        'TC' => ['Turks and Caicos Islands',       '+1649'],
        'TD' => ['Chad',                           '+235'],
        'TG' => ['Togo',                           '+228'],
        'TH' => ['Thailand',                       '+66'],
        'TJ' => ['Tajikistan',                     '+992'],
        'TK' => ['Tokelau',                        '+690'],
        'TL' => ['Timor-Leste',                    '+670'],
        'TM' => ['Turkmenistan',                   '+993'],
        'TN' => ['Tunisia',                        '+216'],
        'TO' => ['Tonga',                          '+676'],
        'TR' => ['Turkey',                         '+90'],
        'TT' => ['Trinidad and Tobago',            '+1868'],
        'TV' => ['Tuvalu',                         '+688'],
        'TW' => ['Taiwan',                         '+886'],
        'TZ' => ['Tanzania',                       '+255'],
        'UA' => ['Ukraine',                        '+380'],
        'UG' => ['Uganda',                         '+256'],
        'US' => ['United States',                  '+1'],
        'UY' => ['Uruguay',                        '+598'],
        'UZ' => ['Uzbekistan',                     '+998'],
        'VC' => ['Saint Vincent and the Grenadines', '+1784'],
        'VE' => ['Venezuela',                      '+58'],
        'VG' => ['British Virgin Islands',         '+1284'],
        'VI' => ['US Virgin Islands',              '+1340'],
        'VN' => ['Vietnam',                        '+84'],
        'VU' => ['Vanuatu',                        '+678'],
        'WF' => ['Wallis and Futuna',              '+681'],
        'WS' => ['Samoa',                          '+685'],
        'XX' => ['Unknown',                        ''],
        'YE' => ['Yemen',                          '+967'],
        'ZA' => ['South Africa',                   '+27'],
        'ZM' => ['Zambia',                         '+260'],
        'ZW' => ['Zimbabwe',                       '+263'],
    ];

    /**
     * Prefix → country code map (longest prefix first for correct matching).
     */
    private static array $prefixes = [
        // 4-digit prefixes (NANP)
        '1242' => 'BS', '1246' => 'BB', '1264' => 'AI', '1268' => 'AG',
        '1284' => 'VG', '1340' => 'VI', '1345' => 'KY', '1441' => 'BM',
        '1473' => 'GD', '1649' => 'TC', '1664' => 'MS', '1670' => 'MP',
        '1671' => 'GU', '1684' => 'AS', '1721' => 'SX', '1758' => 'LC',
        '1767' => 'DM', '1784' => 'VC', '1787' => 'PR', '1809' => 'DO',
        '1829' => 'DO', '1849' => 'DO', '1868' => 'TT', '1869' => 'KN',
        '1876' => 'JM', '1939' => 'PR',

        // 3-digit prefixes
        '211' => 'SS', '212' => 'MA', '213' => 'DZ', '216' => 'TN',
        '218' => 'LY', '220' => 'GM', '221' => 'SN', '222' => 'MR',
        '223' => 'ML', '224' => 'GN', '225' => 'CI', '226' => 'BF',
        '227' => 'NE', '228' => 'TG', '229' => 'BJ', '230' => 'MU',
        '231' => 'LR', '232' => 'SL', '233' => 'GH', '234' => 'NG',
        '235' => 'TD', '236' => 'CF', '237' => 'CM', '238' => 'CV',
        '239' => 'ST', '240' => 'GQ', '241' => 'GA', '242' => 'CG',
        '243' => 'CD', '244' => 'AO', '245' => 'GW', '246' => 'IO',
        '247' => 'AC', '248' => 'SC', '249' => 'SD', '250' => 'RW',
        '251' => 'ET', '252' => 'SO', '253' => 'DJ', '254' => 'KE',
        '255' => 'TZ', '256' => 'UG', '257' => 'BI', '258' => 'MZ',
        '260' => 'ZM', '261' => 'MG', '262' => 'RE', '263' => 'ZW',
        '264' => 'NA', '265' => 'MW', '266' => 'LS', '267' => 'BW',
        '268' => 'SZ', '269' => 'KM', '290' => 'SH', '291' => 'ER',
        '297' => 'AW', '298' => 'FO', '299' => 'GL', '350' => 'GI',
        '351' => 'PT', '352' => 'LU', '353' => 'IE', '354' => 'IS',
        '355' => 'AL', '356' => 'MT', '357' => 'CY', '358' => 'FI',
        '359' => 'BG', '370' => 'LT', '371' => 'LV', '372' => 'EE',
        '373' => 'MD', '374' => 'AM', '375' => 'BY', '376' => 'AD',
        '377' => 'MC', '378' => 'SM', '380' => 'UA', '381' => 'RS',
        '382' => 'ME', '385' => 'HR', '386' => 'SI', '387' => 'BA',
        '389' => 'MK', '420' => 'CZ', '421' => 'SK', '423' => 'LI',
        '500' => 'FK', '501' => 'BZ', '502' => 'GT', '503' => 'SV',
        '504' => 'HN', '505' => 'NI', '506' => 'CR', '507' => 'PA',
        '508' => 'PM', '509' => 'HT', '590' => 'GP', '591' => 'BO',
        '592' => 'GY', '593' => 'EC', '594' => 'GF', '595' => 'PY',
        '596' => 'MQ', '597' => 'SR', '598' => 'UY', '599' => 'CW',
        '670' => 'TL', '672' => 'NF', '673' => 'BN', '674' => 'NR',
        '675' => 'PG', '676' => 'TO', '677' => 'SB', '678' => 'VU',
        '679' => 'FJ', '680' => 'PW', '681' => 'WF', '682' => 'CK',
        '683' => 'NU', '685' => 'WS', '686' => 'KI', '687' => 'NC',
        '688' => 'TV', '689' => 'PF', '690' => 'TK', '691' => 'FM',
        '692' => 'MH', '850' => 'KP', '852' => 'HK', '853' => 'MO',
        '855' => 'KH', '856' => 'LA', '880' => 'BD', '886' => 'TW',
        '960' => 'MV', '961' => 'LB', '962' => 'JO', '963' => 'SY',
        '964' => 'IQ', '965' => 'KW', '966' => 'SA', '967' => 'YE',
        '968' => 'OM', '970' => 'PS', '971' => 'AE', '972' => 'IL',
        '973' => 'BH', '974' => 'QA', '975' => 'BT', '976' => 'MN',
        '977' => 'NP', '992' => 'TJ', '993' => 'TM', '994' => 'AZ',
        '995' => 'GE', '996' => 'KG', '998' => 'UZ',

        // 2-digit prefixes
        '20' => 'EG', '27' => 'ZA', '30' => 'GR', '31' => 'NL',
        '32' => 'BE', '33' => 'FR', '34' => 'ES', '36' => 'HU',
        '39' => 'IT', '40' => 'RO', '41' => 'CH', '43' => 'AT',
        '44' => 'GB', '45' => 'DK', '46' => 'SE', '47' => 'NO',
        '48' => 'PL', '49' => 'DE', '51' => 'PE', '52' => 'MX',
        '53' => 'CU', '54' => 'AR', '55' => 'BR', '56' => 'CL',
        '57' => 'CO', '58' => 'VE', '60' => 'MY', '61' => 'AU',
        '62' => 'ID', '63' => 'PH', '64' => 'NZ', '65' => 'SG',
        '66' => 'TH', '81' => 'JP', '82' => 'KR', '84' => 'VN',
        '86' => 'CN', '90' => 'TR', '91' => 'IN', '92' => 'PK',
        '93' => 'AF', '94' => 'LK', '95' => 'MM', '98' => 'IR',

        // 1-digit prefix
        '7' => 'RU',
        '1' => 'US',
    ];

    // -------------------------------------------------------------------------
    // Public API
    // -------------------------------------------------------------------------

    /**
     * Resolve a phone number and return a rich PhoneResult object.
     *
     * @param string $phoneNumber     The raw phone number in any format.
     * @param string $localCountryCode  ISO 3166-1 alpha-2 fallback for local numbers.
     */
    public static function resolve(string $phoneNumber, string $localCountryCode = 'XX'): PhoneResult
    {
        [$cleanNumber, $format] = self::clean($phoneNumber);

        if ($format === 'local') {
            $code = strtoupper($localCountryCode);
            return self::buildResult($code, $format, $cleanNumber, $localCountryCode);
        }

        $code = self::matchPrefix($cleanNumber) ?? strtoupper($localCountryCode);

        return self::buildResult($code, $format, $cleanNumber, $localCountryCode);
    }

    /**
     * Resolve multiple phone numbers at once.
     *
     * @param  string[]  $phoneNumbers
     * @return PhoneResult[]
     */
    public static function resolveMany(array $phoneNumbers, string $localCountryCode = 'XX'): array
    {
        return array_map(
            fn (string $number) => self::resolve($number, $localCountryCode),
            $phoneNumbers
        );
    }

    /**
     * Normalize a phone number to E.164 format (e.g. +212612345678).
     * Returns null if the number cannot be resolved to a real country.
     */
    public static function normalize(string $phoneNumber, string $localCountryCode = 'XX'): ?string
    {
        $result = self::resolve($phoneNumber, $localCountryCode);

        if (! $result->isValid()) {
            return null;
        }

        return $result->e164;
    }

    /**
     * Backward-compatible: resolve and return just the ISO country code string.
     *
     * @deprecated Use resolve() for richer results.
     */
    public static function resolveCountryCode(string $phoneNumber, string $localCountryCode = 'XX'): string
    {
        return self::resolve($phoneNumber, $localCountryCode)->countryCode;
    }

    /**
     * Get country metadata (name + dial code) for an ISO code.
     * Returns null if the code is unknown.
     */
    public static function getCountryMeta(string $isoCode): ?array
    {
        return self::$countryMeta[strtoupper($isoCode)] ?? null;
    }

    // -------------------------------------------------------------------------
    // Internals
    // -------------------------------------------------------------------------

    /**
     * Strip non-numeric chars, handle 00/+ prefixes, detect format.
     *
     * @return array{string, string}  [cleanNumber, format]
     */
    private static function clean(string $phoneNumber): array
    {
        $stripped = preg_replace('/[^0-9]/', '', $phoneNumber);

        // Double-zero international prefix → strip leading 00
        if (str_starts_with($stripped, '00')) {
            return [ltrim(substr($stripped, 2), '0'), 'international'];
        }

        // + was already removed by preg_replace; if original had + it is international
        if (str_starts_with(ltrim($phoneNumber), '+')) {
            return [$stripped, 'international'];
        }

        // Local number (starts with single 0)
        if (str_starts_with($stripped, '0')) {
            return [$stripped, 'local'];
        }

        // Numeric-only (no prefix character)
        return [$stripped, 'numeric'];
    }

    /**
     * Longest-prefix match against the prefix table.
     */
    private static function matchPrefix(string $cleanNumber): ?string
    {
        foreach (self::$prefixes as $prefix => $code) {
            if (str_starts_with($cleanNumber, (string) $prefix)) {
                return $code;
            }
        }

        return null;
    }

    /**
     * Build a PhoneResult from a resolved country code.
     */
    private static function buildResult(
        string $code,
        string $format,
        string $cleanNumber,
        string $fallback
    ): PhoneResult {
        $meta       = self::$countryMeta[$code] ?? self::$countryMeta['XX'];
        $isResolved = $code !== 'XX' && isset(self::$countryMeta[$code]);
        $dialCode   = $meta[1];

        // Build E.164: dialCode + subscriber digits (strip leading dial digits from cleanNumber)
        $dialDigits = preg_replace('/[^0-9]/', '', $dialCode);
        if ($isResolved && str_starts_with($cleanNumber, $dialDigits)) {
            $e164 = '+' . $cleanNumber;
        } elseif ($isResolved && $format === 'local') {
            $e164 = $dialCode . ltrim($cleanNumber, '0');
        } else {
            $e164 = $isResolved ? '+' . $cleanNumber : '';
        }

        return new PhoneResult(
            countryCode: $code,
            countryName: $meta[0],
            dialCode:    $dialCode,
            isResolved:  $isResolved,
            format:      $format,
            e164:        $e164,
        );
    }
}
