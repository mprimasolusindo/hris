import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
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
import { Check, Plus, Trash2, X } from 'lucide-react';
import { toast } from 'sonner';

type OvertimeRow = {
    id: number;
    employee_id: number;
    employee_name: string | null;
    employee_code: string | null;
    date: string;
    hours: string;
    status: string;
    approver_name: string | null;
};

const statusClass: Record<string, string> = {
    pending: 'bg-amber-500/10 text-amber-700 border-amber-500/30',
    approved: 'bg-emerald-500/10 text-emerald-700 border-emerald-500/30',
    rejected: 'bg-destructive/10 text-destructive border-destructive/30',
};

export default function Index({
    overtimes,
    filters,
    summary,
    statusOptions,
    employees,
    flash,
}: PageProps<{
    overtimes: { data: OvertimeRow[] };
    filters: { status: string; employee_id: string };
    summary: { pending: number; approved: number; rejected: number };
    statusOptions: string[];
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        employee_id: String(employees[0]?.id ?? ''),
        date: '',
        hours: '',
    });

    const applyFilters = (patch: Partial<typeof filters>) => {
        const next = { ...filters, ...patch };
        router.get(route('overtime.index'), {
            status: next.status || undefined,
            employee_id: next.employee_id || undefined,
        });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('overtime.store'), {
            onSuccess: () => {
                setOpen(false);
                form.reset();
            },
        });
    };

    const decide = (row: OvertimeRow, status: 'approved' | 'rejected') => {
        router.put(route('overtime.update', row.id), { status }, { preserveScroll: true });
    };

    const remove = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('overtime.destroy', id), { preserveScroll: true });
    };

    return (
        <HrisLayout>
            <Head title={t('overtime')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">{t('overtime')}</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('newOvertime')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('newOvertime')}</DialogTitle>
                            </DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label>{t('employee')}</Label>
                                    <Select
                                        value={form.data.employee_id}
                                        onValueChange={(v) => form.setData('employee_id', v)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {employees.map((e) => (
                                                <SelectItem key={e.id} value={String(e.id)}>
                                                    {e.full_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>{t('date')}</Label>
                                        <Input
                                            type="date"
                                            value={form.data.date}
                                            onChange={(e) => form.setData('date', e.target.value)}
                                            required
                                        />
                                        {form.errors.date && (
                                            <p className="text-sm text-destructive">{form.errors.date}</p>
                                        )}
                                    </div>
                                    <div className="space-y-2">
                                        <Label>{t('hours')}</Label>
                                        <Input
                                            type="number"
                                            step="0.5"
                                            min="0"
                                            max="24"
                                            value={form.data.hours}
                                            onChange={(e) => form.setData('hours', e.target.value)}
                                            required
                                        />
                                        {form.errors.hours && (
                                            <p className="text-sm text-destructive">{form.errors.hours}</p>
                                        )}
                                    </div>
                                </div>
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">{t('pendingLabel')}</p>
                            <p className="text-2xl font-bold">{summary.pending}</p>
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">{t('approved')}</p>
                            <p className="text-2xl font-bold">{summary.approved}</p>
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">{t('rejected')}</p>
                            <p className="text-2xl font-bold">{summary.rejected}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap gap-3 p-4">
                        <Select
                            value={filters.status || 'all'}
                            onValueChange={(v) => applyFilters({ status: v === 'all' ? '' : v })}
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
                            value={filters.employee_id || 'all'}
                            onValueChange={(v) => applyFilters({ employee_id: v === 'all' ? '' : v })}
                        >
                            <SelectTrigger className="w-56">
                                <SelectValue placeholder={t('employee')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {employees.map((e) => (
                                    <SelectItem key={e.id} value={String(e.id)}>
                                        {e.full_name}
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
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('date')}</TableHead>
                                    <TableHead>{t('hours')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('approver')}</TableHead>
                                    <TableHead className="text-right">{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {overtimes.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="py-8 text-center text-muted-foreground">
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    overtimes.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.employee_name}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {row.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell>{row.date}</TableCell>
                                            <TableCell>{row.hours}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline" className={statusClass[row.status]}>
                                                    {row.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-sm text-muted-foreground">
                                                {row.approver_name ?? '—'}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    {row.status === 'pending' && (
                                                        <>
                                                            <Button
                                                                size="icon"
                                                                variant="ghost"
                                                                aria-label={t('approve')}
                                                                onClick={() => decide(row, 'approved')}
                                                            >
                                                                <Check className="h-4 w-4 text-emerald-600" />
                                                            </Button>
                                                            <Button
                                                                size="icon"
                                                                variant="ghost"
                                                                aria-label={t('reject')}
                                                                onClick={() => decide(row, 'rejected')}
                                                            >
                                                                <X className="h-4 w-4 text-destructive" />
                                                            </Button>
                                                        </>
                                                    )}
                                                    <Button
                                                        size="icon"
                                                        variant="ghost"
                                                        aria-label={t('delete')}
                                                        onClick={() => remove(row.id)}
                                                    >
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </div>
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
