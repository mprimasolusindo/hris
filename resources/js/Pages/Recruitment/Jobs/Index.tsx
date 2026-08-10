import HrisLayout from '@/Layouts/HrisLayout';
import JobPostingForm, {
    emptyJobForm,
    type JobFormLookups,
} from '@/Components/recruitment/JobPostingForm';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
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
import { useEffect, useState } from 'react';
import { Plus } from 'lucide-react';
import { toast } from 'sonner';

type JobRow = {
    id: number;
    title: string;
    code: string | null;
    status: string;
    priority: string;
    employment_type: string;
    headcount: number;
    company_name: string | null;
    application_count: number;
};

export default function Index({
    jobs,
    filters,
    summary,
    statusOptions,
    formOptions,
    flash,
}: PageProps<{
    jobs: JobRow[];
    filters: { status: string; company_id: string };
    summary: { open: number; total: number };
    statusOptions: string[];
    formOptions: JobFormLookups;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const apply = (patch: Partial<typeof filters>) => {
        router.get(route('recruitment.jobs.index'), { ...filters, ...patch });
    };

    return (
        <HrisLayout>
            <Head title={t('jobs')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">{t('jobs')}</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addJob')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent className="max-h-[90vh] max-w-3xl overflow-y-auto">
                            <DialogHeader>
                                <DialogTitle>{t('addJob')}</DialogTitle>
                            </DialogHeader>
                            <JobPostingForm
                                formOptions={formOptions}
                                mode="create"
                                initial={emptyJobForm({
                                    company_id: String(formOptions.companies[0]?.id ?? ''),
                                })}
                                onCancel={() => setOpen(false)}
                                onSuccess={() => setOpen(false)}
                            />
                        </DialogContent>
                    </Dialog>
                </div>

                <div className="grid gap-4 sm:grid-cols-2">
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-xs text-muted-foreground">{t('openReqs')}</p>
                            <p className="text-2xl font-semibold">{summary.open}</p>
                        </CardContent>
                    </Card>
                    <Card>
                        <CardContent className="p-4">
                            <p className="text-xs text-muted-foreground">{t('all')}</p>
                            <p className="text-2xl font-semibold">{summary.total}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap gap-3 p-4">
                        <Select
                            value={filters.status || 'all'}
                            onValueChange={(v) =>
                                apply({ status: v === 'all' ? '' : v })
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder={t('status')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {statusOptions.map((s) => (
                                    <SelectItem key={s} value={s}>
                                        {s}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Select
                            value={filters.company_id || 'all'}
                            onValueChange={(v) =>
                                apply({ company_id: v === 'all' ? '' : v })
                            }
                        >
                            <SelectTrigger className="w-48">
                                <SelectValue placeholder={t('companies')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {formOptions.companies.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('jobCode')}</TableHead>
                                    <TableHead>{t('jobTitle')}</TableHead>
                                    <TableHead>{t('companies')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('priority')}</TableHead>
                                    <TableHead>{t('applications')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {jobs.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    jobs.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="font-mono text-sm">
                                                {row.code || '—'}
                                            </TableCell>
                                            <TableCell>
                                                <Link
                                                    href={route(
                                                        'recruitment.jobs.show',
                                                        row.id,
                                                    )}
                                                    className="font-medium text-primary hover:underline"
                                                >
                                                    {row.title}
                                                </Link>
                                            </TableCell>
                                            <TableCell>{row.company_name}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline">{row.status}</Badge>
                                            </TableCell>
                                            <TableCell>{row.priority}</TableCell>
                                            <TableCell>{row.application_count}</TableCell>
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
