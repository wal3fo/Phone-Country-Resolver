<?php

namespace Wal3fo\PhoneCountry;

class PhoneResult
{
    public function __construct(
        public readonly string $countryCode,
        public readonly string $countryName,
        public readonly string $dialCode,
        public readonly bool   $isResolved,
        public readonly string $format,       // 'international' | 'local' | 'unknown'
        public readonly string $e164,         // normalized E.164 number, e.g. +212612345678
    ) {}

    /**
     * Returns true if the number was resolved to a real country (not XX).
     */
    public function isValid(): bool
    {
        return $this->isResolved && $this->countryCode !== 'XX';
    }

    /**
     * Serialize to array — useful for API responses.
     */
    public function toArray(): array
    {
        return [
            'country_code' => $this->countryCode,
            'country_name' => $this->countryName,
            'dial_code'    => $this->dialCode,
            'is_resolved'  => $this->isResolved,
            'format'       => $this->format,
            'e164'         => $this->e164,
        ];
    }

    public function __toString(): string
    {
        return $this->countryCode;
    }
}
