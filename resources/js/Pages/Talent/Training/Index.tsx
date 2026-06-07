import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type TrainingRow = {
    id: number;
    name: string;
    description: string | null;
    start_date: string | null;
    end_date: string | null;
    location: string | null;
    status: string;
    participants: number;
};

export default function Index({
    items,
    statuses,
    flash,
}: PageProps<{
    items: TrainingRow[];
    statuses: string[];
    summary: { total: number; ongoing: number; completed: number };
}>) {
    const { t } = useLanguage();

    const statusVariant: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        completed: 'default',
        ongoing: 'secondary',
        planned: 'outline',
        cancelled: 'destructive',
    };

    return (
        <MasterCrudPage<TrainingRow>
            title={t('training')}
            pageTitle={t('training')}
            addLabel={t('addTraining')}
            items={items}
            flash={flash}
            detailUrl={(id) => route('training.show', id)}
            columns={[
                { key: 'name', label: t('name') },
                { key: 'start_date', label: t('startDate') },
                { key: 'end_date', label: t('endDate') },
                { key: 'location', label: t('location') },
                { key: 'participants', label: t('participants') },
                {
                    key: 'status',
                    label: t('status'),
                    render: (row) => (
                        <Badge variant={statusVariant[row.status] ?? 'outline'}>
                            {t((row.status as never)) || row.status}
                        </Badge>
                    ),
                },
            ]}
            fields={[
                { name: 'name', label: t('name'), type: 'text', required: true },
                { name: 'description', label: t('description'), type: 'textarea' },
                { name: 'start_date', label: t('startDate'), type: 'date', required: true },
                { name: 'end_date', label: t('endDate'), type: 'date', required: true },
                { name: 'location', label: t('location'), type: 'text' },
                {
                    name: 'status',
                    label: t('status'),
                    type: 'select',
                    required: true,
                    options: statuses.map((s) => ({ value: s, label: t((s as never)) || s })),
                },
            ]}
            initialForm={{
                name: '',
                description: '',
                start_date: '',
                end_date: '',
                location: '',
                status: 'planned',
            }}
            mapRowToForm={(row) => ({
                name: row.name,
                description: row.description ?? '',
                start_date: row.start_date ?? '',
                end_date: row.end_date ?? '',
                location: row.location ?? '',
                status: row.status,
            })}
            storeUrl={route('training.store')}
            updateUrl={(id) => route('training.update', id)}
            destroyUrl={(id) => route('training.destroy', id)}
        />
    );
}
