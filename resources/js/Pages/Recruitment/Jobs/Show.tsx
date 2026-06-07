import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
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
import { ArrowLeft, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type JobData = {
    id: number;
    title: string;
    status: string;
    company_id: number;
    company_name: string | null;
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
    companies,
    statusOptions,
    flash,
}: PageProps<{
    job: JobData;
    applications: ApplicationRow[];
    companies: Array<{ id: number; name: string }>;
    statusOptions: string[];
}>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        company_id: String(job.company_id),
        title: job.title,
        status: job.status,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.put(route('recruitment.jobs.update', job.id));
    };

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
                        <p className="text-sm text-muted-foreground">{job.company_name}</p>
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
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label>{t('companies')}</Label>
                                <Select
                                    value={form.data.company_id}
                                    onValueChange={(v) => form.setData('company_id', v)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {companies.map((c) => (
                                            <SelectItem key={c.id} value={String(c.id)}>
                                                {c.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label>{t('jobTitle')}</Label>
                                <Input
                                    value={form.data.title}
                                    onChange={(e) => form.setData('title', e.target.value)}
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>{t('status')}</Label>
                                <Select
                                    value={form.data.status}
                                    onValueChange={(v) => form.setData('status', v)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {statusOptions.map((s) => (
                                            <SelectItem key={s} value={s}>
                                                {s}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
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
