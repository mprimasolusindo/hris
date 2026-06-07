import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { Card, CardContent } from '@/Components/ui/card';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type ReviewRow = {
    id: number;
    employee_id: number;
    employee_name: string | null;
    reviewer_id: number | null;
    reviewer_name: string | null;
    period_year: number;
    period_quarter: number;
    period: string;
    rating: string;
    goals: string | null;
    notes: string | null;
    status: string;
};

type Option = { id: number; name: string };

export default function Index({
    items,
    employees,
    reviewers,
    statuses,
    summary,
    flash,
}: PageProps<{
    items: ReviewRow[];
    employees: Option[];
    reviewers: Option[];
    statuses: string[];
    summary: { total: number; finalized: number; averageRating: number };
}>) {
    const { t } = useLanguage();

    const statusVariant: Record<string, 'default' | 'secondary' | 'outline'> = {
        finalized: 'default',
        acknowledged: 'secondary',
        submitted: 'outline',
        draft: 'outline',
    };

    return (
        <div className="space-y-4">
            <div className="grid gap-4 sm:grid-cols-3">
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">{t('total')}</p>
                        <p className="text-2xl font-bold">{summary.total}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">{t('finalized')}</p>
                        <p className="text-2xl font-bold">{summary.finalized}</p>
                    </CardContent>
                </Card>
                <Card>
                    <CardContent className="p-4">
                        <p className="text-sm text-muted-foreground">{t('averageRating')}</p>
                        <p className="text-2xl font-bold">{summary.averageRating}</p>
                    </CardContent>
                </Card>
            </div>

            <MasterCrudPage<ReviewRow>
                title={t('performance')}
                pageTitle={t('performance')}
                addLabel={t('addReview')}
                items={items}
                flash={flash}
                columns={[
                    { key: 'employee_name', label: t('employee') },
                    { key: 'period', label: t('period') },
                    { key: 'rating', label: t('rating') },
                    { key: 'reviewer_name', label: t('reviewer') },
                    {
                        key: 'status',
                        label: t('status'),
                        render: (row) => (
                            <Badge variant={statusVariant[row.status] ?? 'outline'}>
                                {t((row.status as never) ?? '') || row.status}
                            </Badge>
                        ),
                    },
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
                        name: 'reviewer_id',
                        label: t('reviewer'),
                        type: 'select',
                        options: [
                            { value: 'none', label: '—' },
                            ...reviewers.map((r) => ({ value: String(r.id), label: r.name })),
                        ],
                    },
                    { name: 'period_year', label: t('year'), type: 'number', required: true, min: 2000, max: 2100 },
                    {
                        name: 'period_quarter',
                        label: t('quarter'),
                        type: 'select',
                        required: true,
                        options: [1, 2, 3, 4].map((q) => ({ value: String(q), label: `Q${q}` })),
                    },
                    { name: 'rating', label: t('rating'), type: 'number', required: true, min: 0, max: 5, step: 0.1 },
                    { name: 'goals', label: t('goals'), type: 'textarea' },
                    { name: 'notes', label: t('notes'), type: 'textarea' },
                    {
                        name: 'status',
                        label: t('status'),
                        type: 'select',
                        required: true,
                        options: statuses.map((s) => ({ value: s, label: t((s as never)) || s })),
                    },
                ]}
                initialForm={{
                    employee_id: '',
                    reviewer_id: 'none',
                    period_year: String(new Date().getFullYear()),
                    period_quarter: '1',
                    rating: '3.0',
                    goals: '',
                    notes: '',
                    status: 'draft',
                }}
                mapRowToForm={(row) => ({
                    employee_id: String(row.employee_id),
                    reviewer_id: row.reviewer_id ? String(row.reviewer_id) : 'none',
                    period_year: String(row.period_year),
                    period_quarter: String(row.period_quarter),
                    rating: row.rating,
                    goals: row.goals ?? '',
                    notes: row.notes ?? '',
                    status: row.status,
                })}
                storeUrl={route('performance.store')}
                updateUrl={(id) => route('performance.update', id)}
                destroyUrl={(id) => route('performance.destroy', id)}
            />
        </div>
    );
}
