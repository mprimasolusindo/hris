import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type TaxRuleRow = {
    id: number;
    name: string;
    rule_type: string | null;
    ptkp_category: string | null;
    gross_min: string | null;
    gross_max: string | null;
    value: string;
};

const idr = (value: string | null) =>
    value === null || value === ''
        ? '—'
        : new Intl.NumberFormat('id-ID').format(Number(value));

const percent = (value: string) => `${(Number(value) * 100).toFixed(2)}%`;

export default function Index({
    items,
    flash,
}: PageProps<{ items: TaxRuleRow[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<TaxRuleRow>
            title={t('taxRules')}
            pageTitle={t('taxRules')}
            addLabel={t('addTaxRule')}
            items={items}
            flash={flash}
            columns={[
                { key: 'name', label: t('name') },
                {
                    key: 'rule_type',
                    label: t('ruleType'),
                    render: (row) =>
                        row.rule_type ? (
                            <Badge variant="secondary">{row.rule_type}</Badge>
                        ) : (
                            '—'
                        ),
                },
                {
                    key: 'ptkp_category',
                    label: t('ptkpCategory'),
                    render: (row) => row.ptkp_category ?? '—',
                },
                {
                    key: 'gross_min',
                    label: t('grossMin'),
                    render: (row) => idr(row.gross_min),
                },
                {
                    key: 'gross_max',
                    label: t('grossMax'),
                    render: (row) => idr(row.gross_max),
                },
                {
                    key: 'value',
                    label: t('rate'),
                    render: (row) => percent(row.value),
                },
            ]}
            fields={[
                { name: 'name', label: t('name'), type: 'text', required: true },
                {
                    name: 'rule_type',
                    label: t('ruleType'),
                    type: 'select',
                    options: [
                        { value: 'ter_monthly', label: 'ter_monthly' },
                        { value: 'ptkp', label: 'ptkp' },
                        { value: 'pasal_17', label: 'pasal_17' },
                    ],
                },
                {
                    name: 'ptkp_category',
                    label: t('ptkpCategory'),
                    type: 'select',
                    options: [
                        { value: 'A', label: 'A (TK/0, TK/1, K/0)' },
                        { value: 'B', label: 'B (TK/2, TK/3, K/1, K/2)' },
                        { value: 'C', label: 'C (K/3)' },
                    ],
                },
                { name: 'gross_min', label: t('grossMin'), type: 'text' },
                { name: 'gross_max', label: t('grossMax'), type: 'text' },
                { name: 'value', label: t('rate'), type: 'text', required: true },
            ]}
            initialForm={{
                name: '',
                rule_type: 'ter_monthly',
                ptkp_category: 'A',
                gross_min: '',
                gross_max: '',
                value: '0',
            }}
            mapRowToForm={(row) => ({
                name: row.name,
                rule_type: row.rule_type ?? '',
                ptkp_category: row.ptkp_category ?? '',
                gross_min: row.gross_min ?? '',
                gross_max: row.gross_max ?? '',
                value: String(row.value),
            })}
            storeUrl={route('payroll.tax-rules.store')}
            updateUrl={(id) => route('payroll.tax-rules.update', id)}
            destroyUrl={(id) => route('payroll.tax-rules.destroy', id)}
        />
    );
}
