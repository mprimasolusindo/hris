import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type InterviewRow = {
    id: number;
    application_id: number;
    candidate_name: string | null;
    job_title: string | null;
    scheduled_at: string | null;
    scheduled_at_label: string | null;
    interviewer_name: string;
    location: string | null;
    status: string;
    feedback: string | null;
    rating: number | null;
};

type Option = { id: number; name: string };

export default function Index({
    items,
    applications,
    statuses,
    flash,
}: PageProps<{
    items: InterviewRow[];
    applications: Option[];
    statuses: string[];
    summary: { total: number; scheduled: number; completed: number };
}>) {
    const { t } = useLanguage();

    const statusVariant: Record<string, 'default' | 'secondary' | 'outline' | 'destructive'> = {
        completed: 'default',
        scheduled: 'secondary',
        cancelled: 'destructive',
        no_show: 'destructive',
    };

    return (
        <MasterCrudPage<InterviewRow>
            title={t('interviews')}
            pageTitle={t('interviews')}
            addLabel={t('scheduleInterview')}
            items={items}
            flash={flash}
            columns={[
                { key: 'candidate_name', label: t('candidate') },
                { key: 'job_title', label: t('jobs') },
                { key: 'scheduled_at_label', label: t('scheduledAt') },
                { key: 'interviewer_name', label: t('interviewer') },
                { key: 'rating', label: t('rating') },
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
                {
                    name: 'application_id',
                    label: t('application'),
                    type: 'select',
                    required: true,
                    options: applications.map((a) => ({ value: String(a.id), label: a.name })),
                },
                { name: 'scheduled_at', label: t('scheduledAt'), type: 'datetime-local', required: true },
                { name: 'interviewer_name', label: t('interviewer'), type: 'text', required: true },
                { name: 'location', label: t('location'), type: 'text' },
                {
                    name: 'status',
                    label: t('status'),
                    type: 'select',
                    required: true,
                    options: statuses.map((s) => ({ value: s, label: t((s as never)) || s })),
                },
                { name: 'rating', label: t('rating'), type: 'number', min: 1, max: 5 },
                { name: 'feedback', label: t('feedback'), type: 'textarea' },
            ]}
            initialForm={{
                application_id: '',
                scheduled_at: '',
                interviewer_name: '',
                location: '',
                status: 'scheduled',
                rating: '',
                feedback: '',
            }}
            mapRowToForm={(row) => ({
                application_id: String(row.application_id),
                scheduled_at: row.scheduled_at ?? '',
                interviewer_name: row.interviewer_name,
                location: row.location ?? '',
                status: row.status,
                rating: row.rating != null ? String(row.rating) : '',
                feedback: row.feedback ?? '',
            })}
            storeUrl={route('recruitment.interviews.store')}
            updateUrl={(id) => route('recruitment.interviews.update', id)}
            destroyUrl={(id) => route('recruitment.interviews.destroy', id)}
        />
    );
}
