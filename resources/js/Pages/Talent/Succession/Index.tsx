import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type PlanRow = {
    id: number;
    position_id: number;
    position_name: string | null;
    successor_id: number;
    successor_name: string | null;
    incumbent_id: number | null;
    incumbent_name: string | null;
    readiness: string;
    notes: string | null;
};

export default function Index({
    items,
    positions,
    employees,
    readinessOptions,
    flash,
}: PageProps<{
    items: PlanRow[];
    positions: Array<{ id: number; name: string }>;
    employees: Array<{ id: number; name: string }>;
    readinessOptions: string[];
    summary: { total: number; readyNow: number };
}>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<PlanRow>
            title={t('succession')}
            pageTitle={t('succession')}
            addLabel={t('addSuccessionPlan')}
            items={items}
            flash={flash}
            columns={[
                { key: 'position_name', label: t('position') },
                { key: 'incumbent_name', label: t('incumbent') },
                { key: 'successor_name', label: t('successor') },
                {
                    key: 'readiness',
                    label: t('readiness'),
                    render: (row) => <Badge variant="outline">{t((row.readiness as never)) || row.readiness}</Badge>,
                },
            ]}
            fields={[
                {
                    name: 'position_id',
                    label: t('position'),
                    type: 'select',
                    required: true,
                    options: positions.map((p) => ({ value: String(p.id), label: p.name })),
                },
                {
                    name: 'successor_id',
                    label: t('successor'),
                    type: 'select',
                    required: true,
                    options: employees.map((e) => ({ value: String(e.id), label: e.name })),
                },
                {
                    name: 'incumbent_id',
                    label: t('incumbent'),
                    type: 'select',
                    options: [
                        { value: 'none', label: '—' },
                        ...employees.map((e) => ({ value: String(e.id), label: e.name })),
                    ],
                },
                {
                    name: 'readiness',
                    label: t('readiness'),
                    type: 'select',
                    required: true,
                    options: readinessOptions.map((r) => ({ value: r, label: t((r as never)) || r })),
                },
                { name: 'notes', label: t('notes'), type: 'textarea' },
            ]}
            initialForm={{
                position_id: '',
                successor_id: '',
                incumbent_id: 'none',
                readiness: readinessOptions[0] ?? 'ready_now',
                notes: '',
            }}
            mapRowToForm={(row) => ({
                position_id: String(row.position_id),
                successor_id: String(row.successor_id),
                incumbent_id: row.incumbent_id ? String(row.incumbent_id) : 'none',
                readiness: row.readiness,
                notes: row.notes ?? '',
            })}
            storeUrl={route('succession.store')}
            updateUrl={(id) => route('succession.update', id)}
            destroyUrl={(id) => route('succession.destroy', id)}
        />
    );
}
