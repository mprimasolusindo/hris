import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type PoolRow = {
    id: number;
    employee_id: number;
    employee_name: string | null;
    employee_code: string | null;
    readiness: string;
    potential: string;
    notes: string | null;
};

type Option = { id: number; name: string };

export default function Index({
    items,
    employees,
    readinessOptions,
    potentialOptions,
    flash,
}: PageProps<{
    items: PoolRow[];
    employees: Option[];
    readinessOptions: string[];
    potentialOptions: string[];
    summary: { total: number; highPotential: number; readyNow: number };
}>) {
    const { t } = useLanguage();

    const potentialVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
        high: 'default',
        medium: 'secondary',
        low: 'outline',
    };

    return (
        <MasterCrudPage<PoolRow>
            title={t('talentPool')}
            pageTitle={t('talentPool')}
            addLabel={t('addTalentPoolEntry')}
            items={items}
            flash={flash}
            columns={[
                { key: 'employee_name', label: t('employee') },
                {
                    key: 'readiness',
                    label: t('readiness'),
                    render: (row) => <Badge variant="outline">{t((row.readiness as never)) || row.readiness}</Badge>,
                },
                {
                    key: 'potential',
                    label: t('potential'),
                    render: (row) => (
                        <Badge variant={potentialVariant[row.potential] ?? 'outline'}>
                            {t((row.potential as never)) || row.potential}
                        </Badge>
                    ),
                },
                { key: 'notes', label: t('notes') },
            ]}
            fields={[
                {
                    name: 'employee_id',
                    label: t('employee'),
                    type: 'select',
                    required: true,
                    options: employees.map((e) => ({ value: String(e.id), label: e.name })),
                },
                {
                    name: 'readiness',
                    label: t('readiness'),
                    type: 'select',
                    required: true,
                    options: readinessOptions.map((r) => ({ value: r, label: t((r as never)) || r })),
                },
                {
                    name: 'potential',
                    label: t('potential'),
                    type: 'select',
                    required: true,
                    options: potentialOptions.map((p) => ({ value: p, label: t((p as never)) || p })),
                },
                { name: 'notes', label: t('notes'), type: 'textarea' },
            ]}
            initialForm={{
                employee_id: '',
                readiness: readinessOptions[0] ?? 'ready_now',
                potential: potentialOptions[0] ?? 'medium',
                notes: '',
            }}
            mapRowToForm={(row) => ({
                employee_id: String(row.employee_id),
                readiness: row.readiness,
                potential: row.potential,
                notes: row.notes ?? '',
            })}
            storeUrl={route('talent-pool.store')}
            updateUrl={(id) => route('talent-pool.update', id)}
            destroyUrl={(id) => route('talent-pool.destroy', id)}
        />
    );
}
