export function formatIdr(value: string | number | null | undefined): string {
    const n = Number(value ?? 0);
    return `Rp ${n.toLocaleString('id-ID')}`;
}

export function sanitizeDigits(value: string, maxLength?: number): string {
    const digits = value.replace(/\D/g, '');
    return typeof maxLength === 'number' ? digits.slice(0, maxLength) : digits;
}

export const PTKP_OPTIONS = [
    'TK/0',
    'TK/1',
    'TK/2',
    'TK/3',
    'K/0',
    'K/1',
    'K/2',
    'K/3',
] as const;

export const TAX_METHOD_OPTIONS = [
    { value: 'ter_monthly', labelKey: 'terMonthly' },
    { value: 'annual_adjustment', labelKey: 'annualAdjustment' },
] as const;

export const DOCUMENT_CATEGORIES = [
    'ktp',
    'npwp',
    'contract',
    'certificate',
    'other',
] as const;
