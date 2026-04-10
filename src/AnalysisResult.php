<?php

namespace Wal3fo\PhoneCountry;

/**
 * Rich result object returned by PhoneCountryService::analyze().
 */
class AnalysisResult
{
    public function __construct(
        // ── Core resolution ───────────────────────────────────────────────
        public readonly string  $raw,               // original input as-is
        public readonly string  $normalized,         // cleaned digits (no +/spaces/dashes)
        public readonly string  $e164,               // E.164 canonical form  e.g. +212612345678
        public readonly string  $nationalNumber,     // local number without country prefix
        public readonly string  $dialingCode,        // numeric calling code  e.g. "212"

        // ── Country metadata ──────────────────────────────────────────────
        public readonly string  $countryCode,        // ISO 3166-1 alpha-2  e.g. "MA"
        public readonly string  $countryName,        // Full English name    e.g. "Morocco"
        public readonly string  $flag,               // Emoji flag           e.g. "🇲🇦"
        public readonly string  $region,             // World region         e.g. "Africa"
        public readonly string  $continent,          // Continent            e.g. "Africa"
        public readonly string  $capital,            // Capital city         e.g. "Rabat"
        public readonly string  $currency,           // ISO 4217 code        e.g. "MAD"
        public readonly string  $currencyName,       // Currency name        e.g. "Moroccan Dirham"
        public readonly string  $language,           // Primary language     e.g. "Arabic"
        public readonly string  $timezone,           // Primary TZ           e.g. "Africa/Casablanca"

        // ── Number analysis ───────────────────────────────────────────────
        public readonly string  $numberType,         // MOBILE | FIXED_LINE | TOLL_FREE | UNKNOWN
        public readonly bool    $isValid,            // passes basic length/format checks
        public readonly bool    $isPossible,         // plausible length for country
        public readonly int     $digitCount,         // total digit count of nationalNumber
        public readonly string  $formatNational,     // national format  e.g. "06 12 34 56 78"
        public readonly string  $formatInternational,// intl format       e.g. "+212 6 12 34 56 78"

        // ── Input format hints ────────────────────────────────────────────
        public readonly string  $inputFormat,        // E164 | DOUBLE_ZERO | NUMERIC | LOCAL
        public readonly bool    $usedFallback,       // true if localCountryCode fallback was used

        // ── Resolved at ───────────────────────────────────────────────────
        public readonly string  $resolvedAt,         // ISO 8601 timestamp
    ) {}

    // ── Convenience helpers ───────────────────────────────────────────────

    /** Plain associative array – useful for JSON responses. */
    public function toArray(): array
    {
        return [
            'raw'                  => $this->raw,
            'normalized'           => $this->normalized,
            'e164'                 => $this->e164,
            'national_number'      => $this->nationalNumber,
            'dialing_code'         => $this->dialingCode,
            'country_code'         => $this->countryCode,
            'country_name'         => $this->countryName,
            'flag'                 => $this->flag,
            'region'               => $this->region,
            'continent'            => $this->continent,
            'capital'              => $this->capital,
            'currency'             => $this->currency,
            'currency_name'        => $this->currencyName,
            'language'             => $this->language,
            'timezone'             => $this->timezone,
            'number_type'          => $this->numberType,
            'is_valid'             => $this->isValid,
            'is_possible'          => $this->isPossible,
            'digit_count'          => $this->digitCount,
            'format_national'      => $this->formatNational,
            'format_international' => $this->formatInternational,
            'input_format'         => $this->inputFormat,
            'used_fallback'        => $this->usedFallback,
            'resolved_at'          => $this->resolvedAt,
        ];
    }

    /** JSON string. */
    public function toJson(int $flags = JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE): string
    {
        return json_encode($this->toArray(), $flags);
    }

    /**
     * Self-contained HTML card — no external dependencies, inline CSS.
     * Ideal for debug views, Blade partials, or API responses.
     */
    public function toHtml(): string
    {
        $esc = fn(string $v): string => htmlspecialchars($v, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8');

        $validBadge   = $this->isValid
            ? '<span class="pcr-badge pcr-badge--valid">✓ Valid</span>'
            : '<span class="pcr-badge pcr-badge--invalid">✗ Invalid</span>';

        $fallbackBadge = $this->usedFallback
            ? '<span class="pcr-badge pcr-badge--fallback">Fallback used</span>'
            : '';

        $typeBadge = '<span class="pcr-badge pcr-badge--type">' . $esc($this->numberType) . '</span>';

        $rows = [
            ['📞 Raw input',          $this->raw],
            ['🔢 Normalized',          $this->normalized],
            ['🌐 E.164',               $this->e164],
            ['📱 National number',     $this->nationalNumber],
            ['🔑 Dialing code',        '+' . $this->dialingCode],
            ['🏳️ Country code',        $this->countryCode],
            ['🗺️ Country',             $this->flag . ' ' . $this->countryName],
            ['🌍 Region / Continent',  $this->region . ' / ' . $this->continent],
            ['🏛️ Capital',             $this->capital],
            ['💱 Currency',            $this->currency . ' — ' . $this->currencyName],
            ['🗣️ Language',            $this->language],
            ['🕐 Timezone',            $this->timezone],
            ['📐 Format (national)',   $this->formatNational],
            ['📐 Format (intl)',       $this->formatInternational],
            ['🔍 Input format',        $this->inputFormat],
            ['🔢 Digit count',         (string) $this->digitCount],
            ['🕒 Resolved at',         $this->resolvedAt],
        ];

        $tableRows = '';
        foreach ($rows as [$label, $value]) {
            $tableRows .= sprintf(
                '<tr><th>%s</th><td>%s</td></tr>',
                $esc($label),
                $esc($value)
            );
        }

        return <<<HTML
<style>
  .pcr-card {
    font-family: 'Segoe UI', system-ui, sans-serif;
    max-width: 580px;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 20px rgba(0,0,0,.07);
    background: #fff;
    color: #1e293b;
  }
  .pcr-header {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a5f 100%);
    color: #fff;
    padding: 18px 22px 14px;
    display: flex;
    align-items: center;
    gap: 14px;
  }
  .pcr-flag { font-size: 2.6rem; line-height: 1; }
  .pcr-header-text h2 { margin: 0 0 4px; font-size: 1.25rem; font-weight: 700; }
  .pcr-header-text p  { margin: 0; font-size: .85rem; opacity: .75; }
  .pcr-badges {
    display: flex;
    flex-wrap: wrap;
    gap: 6px;
    padding: 12px 22px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
  }
  .pcr-badge {
    display: inline-block;
    padding: 3px 10px;
    border-radius: 20px;
    font-size: .75rem;
    font-weight: 600;
    letter-spacing: .02em;
  }
  .pcr-badge--valid   { background:#dcfce7; color:#166534; }
  .pcr-badge--invalid { background:#fee2e2; color:#991b1b; }
  .pcr-badge--type    { background:#dbeafe; color:#1d4ed8; }
  .pcr-badge--fallback{ background:#fef9c3; color:#854d0e; }
  .pcr-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
  .pcr-table th, .pcr-table td { padding: 9px 16px; text-align: left; border-bottom: 1px solid #f1f5f9; }
  .pcr-table th { color: #64748b; font-weight: 500; white-space: nowrap; width: 42%; }
  .pcr-table td { color: #0f172a; font-family: 'Courier New', monospace; font-size: .82rem; }
  .pcr-table tr:last-child th, .pcr-table tr:last-child td { border-bottom: none; }
  .pcr-table tr:hover td, .pcr-table tr:hover th { background: #f8fafc; }
</style>
<div class="pcr-card">
  <div class="pcr-header">
    <div class="pcr-flag">{$this->flag}</div>
    <div class="pcr-header-text">
      <h2>{$this->countryName} (+{$this->dialingCode})</h2>
      <p>{$this->e164}</p>
    </div>
  </div>
  <div class="pcr-badges">
    {$validBadge}
    {$typeBadge}
    {$fallbackBadge}
  </div>
  <table class="pcr-table">
    {$tableRows}
  </table>
</div>
HTML;
    }
}
