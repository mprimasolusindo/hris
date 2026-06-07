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
import { Plus, X } from 'lucide-react';
import { toast } from 'sonner';

type LeaveRow = {
    id: number;
    employee_name: string | null;
    employee_code: string | null;
    type: string;
    start_date: string;
    end_date: string;
    status: string;
};

const statusClass: Record<string, string> = {
    pending: 'bg-amber-500/10 text-amber-700 border-amber-500/30',
    approved: 'bg-emerald-500/10 text-emerald-700 border-emerald-500/30',
    rejected: 'bg-destructive/10 text-destructive border-destructive/30',
    cancelled: 'bg-muted text-muted-foreground border-border',
};

export default function Index({
    leaves,
    filters,
    typeOptions,
    employees,
    flash,
}: PageProps<{
    leaves: { data: LeaveRow[]; links: unknown[] };
    filters: { status: string; type: string };
    typeOptions: string[];
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        employee_id: String(employees[0]?.id ?? ''),
        type: typeOptions[0] ?? 'annual',
        start_date: '',
        end_date: '',
    });

    const applyFilters = (patch: Partial<typeof filters>) => {
        router.get(route('leave.index'), { ...filters, ...patch });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('leave.store'), {
            onSuccess: () => {
                setOpen(false);
                form.reset();
            },
        });
    };

    const cancelLeave = (id: number) => {
        if (!window.confirm(t('cancel') + '?')) return;
        router.patch(route('leave.cancel', id));
    };

    return (
        <HrisLayout>
            <Head title={t('leaveRequests')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">{t('leaveRequests')}</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('newLeaveRequest')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('newLeaveRequest')}</DialogTitle>
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
                                <div className="space-y-2">
                                    <Label>{t('leaveType')}</Label>
                                    <Select
                                        value={form.data.type}
                                        onValueChange={(v) => form.setData('type', v)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {typeOptions.map((tp) => (
                                                <SelectItem key={tp} value={tp}>
                                                    {tp}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>{t('startDate')}</Label>
                                        <Input
                                            type="date"
                                            value={form.data.start_date}
                                            onChange={(e) =>
                                                form.setData('start_date', e.target.value)
                                            }
                                            required
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>{t('endDate')}</Label>
                                        <Input
                                            type="date"
                                            value={form.data.end_date}
                                            onChange={(e) =>
                                                form.setData('end_date', e.target.value)
                                            }
                                            required
                                        />
                                    </div>
                                </div>
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap gap-3 p-4">
                        <Select
                            value={filters.status || 'all'}
                            onValueChange={(v) =>
                                applyFilters({ status: v === 'all' ? '' : v })
                            }
                        >
                            <SelectTrigger className="w-40">
                                <SelectValue placeholder={t('status')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                <SelectItem value="pending">{t('pendingLabel')}</SelectItem>
                                <SelectItem value="approved">{t('approved')}</SelectItem>
                                <SelectItem value="rejected">{t('rejected')}</SelectItem>
                                <SelectItem value="cancelled">{t('cancelled')}</SelectItem>
                            </SelectContent>
                        </Select>
                        <Select
                            value={filters.type || 'all'}
                            onValueChange={(v) =>
                                applyFilters({ type: v === 'all' ? '' : v })
                            }
                        >
                            <SelectTrigger className="w-44">
                                <SelectValue placeholder={t('leaveType')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {typeOptions.map((tp) => (
                                    <SelectItem key={tp} value={tp}>
                                        {tp}
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
                                    <TableHead>{t('leaveType')}</TableHead>
                                    <TableHead>{t('startDate')}</TableHead>
                                    <TableHead>{t('endDate')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {leaves.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    leaves.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.employee_name}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {row.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell>{row.type}</TableCell>
                                            <TableCell>{row.start_date}</TableCell>
                                            <TableCell>{row.end_date}</TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant="outline"
                                                    className={statusClass[row.status]}
                                                >
                                                    {row.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {row.status === 'pending' && (
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() => cancelLeave(row.id)}
                                                    >
                                                        <X className="h-4 w-4" />
                                                    </Button>
                                                )}
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
