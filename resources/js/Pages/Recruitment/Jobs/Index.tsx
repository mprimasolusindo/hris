import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
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
import { FormEventHandler, useEffect, useState } from 'react';
import { Plus } from 'lucide-react';
import { toast } from 'sonner';

type JobRow = {
    id: number;
    title: string;
    status: string;
    company_name: string | null;
    application_count: number;
};

export default function Index({
    jobs,
    filters,
    summary,
    companies,
    statusOptions,
    flash,
}: PageProps<{
    jobs: JobRow[];
    filters: { status: string; company_id: string };
    summary: { open: number; total: number };
    companies: Array<{ id: number; name: string }>;
    statusOptions: string[];
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        company_id: String(companies[0]?.id ?? ''),
        title: '',
        status: 'open',
    });

    const apply = (patch: Partial<typeof filters>) => {
        router.get(route('recruitment.jobs.index'), { ...filters, ...patch });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('recruitment.jobs.store'), {
            onSuccess: () => {
                setOpen(false);
                form.reset();
            },
        });
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
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('addJob')}</DialogTitle>
                            </DialogHeader>
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
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </form>
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
                                {companies.map((c) => (
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
                                    <TableHead>{t('jobTitle')}</TableHead>
                                    <TableHead>{t('companies')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('applications')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {jobs.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    jobs.map((row) => (
                                        <TableRow key={row.id}>
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
