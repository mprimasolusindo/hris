<?php

namespace App\Support\Indonesia;

/**
 * Indonesian ID field validation helpers.
 *
 * NIK: 16-digit population ID (Dukcapil).
 * NPWP: taxpayer ID format per DJP (PMK 112/2022 formatting guidance).
 * PTKP tax_status: PMK 168/2023 TER categories TK/0..K/3.
 *
 * @see .cursor/agents/hr-research-indonesia.md for regulation updates.
 */
final class IdValidators
{
    /** @return array<int, string> */
    public static function nikRules(): array
    {
        return ['nullable', 'string', 'regex:/^\d{16}$/'];
    }

    /** @return array<int, string> */
    public static function npwpRules(): array
    {
        return ['nullable', 'string', 'max:32'];
    }

    /** @return array<int, string> */
    public static function taxStatusRules(): array
    {
        return ['nullable', 'string', 'in:TK/0,TK/1,TK/2,TK/3,K/0,K/1,K/2,K/3'];
    }

    /** @return array<int, string> */
    public static function taxMethodRules(): array
    {
        return ['nullable', 'string', 'in:ter_monthly,annual_adjustment'];
    }

    /** @return array<int, string> */
    public static function bpjsNumberRules(): array
    {
        return ['nullable', 'string', 'max:32'];
    }
}
