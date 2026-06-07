<?php

namespace App\Support\Payroll;

/**
 * Maps an employee PTKP tax status (TK/0, K/1, …) to the PPh21 TER
 * category (A / B / C) used by the monthly Tarif Efektif Rata-rata table.
 *
 * Grouping per PMK 168/2023 (effective 1 Jan 2024):
 *   - A: TK/0, TK/1, K/0          (PTKP Rp 54–58,5 jt/yr)
 *   - B: TK/2, TK/3, K/1, K/2     (PTKP Rp 63–67,5 jt/yr)
 *   - C: K/3                      (PTKP Rp 72 jt/yr)
 *
 * Unknown / null statuses fall back to category A (most conservative,
 * lowest PTKP) so tax is never silently skipped.
 */
class TerCategoryResolver
{
    /** @var array<string, string> */
    private const MAP = [
        'TK/0' => 'A',
        'TK/1' => 'A',
        'K/0' => 'A',
        'TK/2' => 'B',
        'TK/3' => 'B',
        'K/1' => 'B',
        'K/2' => 'B',
        'K/3' => 'C',
    ];

    public static function resolve(?string $taxStatus): string
    {
        if ($taxStatus === null) {
            return 'A';
        }

        $normalized = strtoupper(trim($taxStatus));

        return self::MAP[$normalized] ?? 'A';
    }
}
