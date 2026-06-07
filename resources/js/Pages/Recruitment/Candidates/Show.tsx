import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Alert, AlertDescription } from '@/Components/ui/alert';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import { FormEventHandler, useEffect } from 'react';
import { AlertTriangle, ArrowLeft, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type CandidateData = {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    created_at: string | null;
};

type ApplicationRow = {
    id: number;
    job_title: string | null;
    company_name: string | null;
    stage: string;
    created_at: string | null;
};

export default function Show({
    candidate,
    duplicateEmail,
    applications,
    jobs,
    flash,
}: PageProps<{
    candidate: CandidateData;
    duplicateEmail: boolean;
    applications: ApplicationRow[];
    jobs: Array<{ id: number; title: string }>;
}>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        name: candidate.name,
        email: candidate.email ?? '',
        phone: candidate.phone ?? '',
    });

    const applyForm = useForm({
        candidate_id: candidate.id,
        job_id: String(jobs[0]?.id ?? ''),
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.put(route('recruitment.candidates.update', candidate.id));
    };

    const applyToJob: FormEventHandler = (e) => {
        e.preventDefault();
        if (!applyForm.data.job_id) return;
        applyForm.post(route('recruitment.applications.store'));
    };

    const destroy = () => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('recruitment.candidates.destroy', candidate.id));
    };

    return (
        <HrisLayout>
            <Head title={candidate.name} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('recruitment.candidates.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <h1 className="text-2xl font-bold text-foreground">{candidate.name}</h1>
                </div>

                {duplicateEmail && (
                    <Alert variant="destructive">
                        <AlertTriangle className="h-4 w-4" />
                        <AlertDescription>{t('duplicateEmailWarning')}</AlertDescription>
                    </Alert>
                )}

                <Card>
                    <CardHeader>
                        <CardTitle>{t('edit')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label>{t('name')}</Label>
                                <Input
                                    value={form.data.name}
                                    onChange={(e) => form.setData('name', e.target.value)}
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>{t('email')}</Label>
                                <Input
                                    type="email"
                                    value={form.data.email}
                                    onChange={(e) => form.setData('email', e.target.value)}
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>{t('phone')}</Label>
                                <Input
                                    value={form.data.phone}
                                    onChange={(e) => form.setData('phone', e.target.value)}
                                />
                            </div>
                            <div className="flex justify-between gap-2">
                                <Button type="button" variant="destructive" onClick={destroy}>
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    {t('delete')}
                                </Button>
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('applyToJob')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={applyToJob} className="flex flex-wrap items-end gap-3">
                            <div className="min-w-[200px] flex-1 space-y-2">
                                <Label>{t('jobs')}</Label>
                                <Select
                                    value={applyForm.data.job_id}
                                    onValueChange={(v) => applyForm.setData('job_id', v)}
                                >
                                    <SelectTrigger>
                                        <SelectValue placeholder={t('jobs')} />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {jobs.map((job) => (
                                            <SelectItem key={job.id} value={String(job.id)}>
                                                {job.title}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button type="submit" disabled={applyForm.processing || jobs.length === 0}>
                                {t('applyToJob')}
                            </Button>
                        </form>
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
                                    <TableHead>{t('jobs')}</TableHead>
                                    <TableHead>{t('companies')}</TableHead>
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
                                            <TableCell>{app.job_title}</TableCell>
                                            <TableCell>{app.company_name}</TableCell>
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
