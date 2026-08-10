import HrisLayout from '@/Layouts/HrisLayout';
import JobPostingForm, {
    emptyJobForm,
    type JobFormLookups,
} from '@/Components/recruitment/JobPostingForm';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import { useEffect } from 'react';
import { ArrowLeft } from 'lucide-react';
import { toast } from 'sonner';

type JobData = {
    id: number;
    title: string;
    code: string | null;
    status: string;
    company_id: number;
    company_name: string | null;
    site_id: number | null;
    department_id: number | null;
    position_id: number | null;
    employment_type: string;
    headcount: number;
    priority: string;
    opened_at: string | null;
    target_close_date: string | null;
    hiring_manager_id: number | null;
    recruiter_id: number | null;
    salary_min: string | null;
    salary_max: string | null;
    currency: string;
    description: string | null;
    requirements: string | null;
    benefits: string | null;
    location_note: string | null;
    created_at: string | null;
};

type ApplicationRow = {
    id: number;
    candidate_name: string | null;
    candidate_email: string | null;
    stage: string;
    created_at: string | null;
};

export default function Show({
    job,
    applications,
    formOptions,
    flash,
}: PageProps<{
    job: JobData;
    applications: ApplicationRow[];
    formOptions: JobFormLookups;
    statusOptions: string[];
}>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const destroy = () => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('recruitment.jobs.destroy', job.id));
    };

    return (
        <HrisLayout>
            <Head title={job.title} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('recruitment.jobs.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">{job.title}</h1>
                        <p className="text-sm text-muted-foreground">
                            {job.company_name}
                            {job.code ? ` · ${job.code}` : ''}
                        </p>
                    </div>
                    <Badge className="ml-auto" variant="outline">
                        {job.status}
                    </Badge>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('editJob')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <JobPostingForm
                            formOptions={formOptions}
                            mode="edit"
                            jobId={job.id}
                            showDelete
                            onDelete={destroy}
                            initial={emptyJobForm({
                                company_id: String(job.company_id),
                                site_id: job.site_id ? String(job.site_id) : '',
                                department_id: job.department_id
                                    ? String(job.department_id)
                                    : '',
                                position_id: job.position_id
                                    ? String(job.position_id)
                                    : '',
                                title: job.title,
                                code: job.code ?? '',
                                employment_type: job.employment_type || 'permanent',
                                headcount: String(job.headcount ?? 1),
                                priority: job.priority || 'medium',
                                opened_at: job.opened_at ?? '',
                                target_close_date: job.target_close_date ?? '',
                                hiring_manager_id: job.hiring_manager_id
                                    ? String(job.hiring_manager_id)
                                    : '',
                                recruiter_id: job.recruiter_id
                                    ? String(job.recruiter_id)
                                    : '',
                                salary_min: job.salary_min ?? '',
                                salary_max: job.salary_max ?? '',
                                currency: job.currency || 'IDR',
                                description: job.description ?? '',
                                requirements: job.requirements ?? '',
                                benefits: job.benefits ?? '',
                                location_note: job.location_note ?? '',
                                status: job.status,
                            })}
                        />
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('applications')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('candidates')}</TableHead>
                                    <TableHead>{t('email')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {applications.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={3}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    applications.map((app) => (
                                        <TableRow key={app.id}>
                                            <TableCell>{app.candidate_name}</TableCell>
                                            <TableCell>{app.candidate_email}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline">{app.stage}</Badge>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
