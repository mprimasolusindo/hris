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
import { Textarea } from '@/Components/ui/textarea';
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
    reason: string | null;
    status: string;
};

type LinkedEmployee = {
    id: number;
    full_name: string;
    employee_code: string;
} | null;

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
    selfService = false,
    linkedEmployee = null,
    canCreate = true,
    canCancel = true,
    flash,
}: PageProps<{
    leaves: { data: LeaveRow[]; links: unknown[] };
    filters: { status: string; type: string };
    typeOptions: string[];
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
    selfService?: boolean;
    linkedEmployee?: LinkedEmployee;
    canCreate?: boolean;
    canCancel?: boolean;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    const form = useForm({
        employee_id: String(
            selfService
                ? (linkedEmployee?.id ?? employees[0]?.id ?? '')
                : (employees[0]?.id ?? ''),
        ),
        type: typeOptions[0] ?? 'annual',
        start_date: '',
        end_date: '',
        reason: '',
    });

    const applyFilters = (patch: Partial<typeof filters>) => {
        router.get(route('leave.index'), { ...filters, ...patch });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('leave.store'), {
            onSuccess: () => {
                setOpen(false);
                form.reset('start_date', 'end_date', 'reason');
            },
        });
    };

    const cancelLeave = (id: number) => {
        if (!window.confirm(t('cancel') + '?')) return;
        router.patch(route('leave.cancel', id));
    };

    const openDisabled = selfService && !linkedEmployee;

    return (
        <HrisLayout>
            <Head title={t('leaveRequests')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">{t('leaveRequests')}</h1>
                        {selfService && (
                            <p className="text-sm text-muted-foreground">{t('leaveOnlineSubtitle')}</p>
                        )}
                    </div>
                    {canCreate && (
                        <Dialog open={open} onOpenChange={setOpen}>
                            <DialogTrigger asChild>
                                <Button disabled={openDisabled}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t('newLeaveRequest')}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>{t('newLeaveRequest')}</DialogTitle>
                                </DialogHeader>
                                {openDisabled ? (
                                    <p className="text-sm text-muted-foreground">
                                        {t('leaveNotLinked')}
                                    </p>
                                ) : (
                                    <form onSubmit={submit} className="space-y-4">
                                        {!selfService ? (
                                            <div className="space-y-2">
                                                <Label>{t('employee')}</Label>
                                                <Select
                                                    value={form.data.employee_id}
                                                    onValueChange={(v) =>
                                                        form.setData('employee_id', v)
                                                    }
                                                >
                                                    <SelectTrigger>
                                                        <SelectValue />
                                                    </SelectTrigger>
                                                    <SelectContent>
                                                        {employees.map((emp) => (
                                                            <SelectItem
                                                                key={emp.id}
                                                                value={String(emp.id)}
                                                            >
                                                                {emp.full_name}
                                                            </SelectItem>
                                                        ))}
                                                    </SelectContent>
                                                </Select>
                                            </div>
                                        ) : (
                                            linkedEmployee && (
                                                <div className="rounded-md border bg-muted/40 px-3 py-2 text-sm">
                                                    <span className="text-muted-foreground">
                                                        {t('employee')}:{' '}
                                                    </span>
                                                    {linkedEmployee.full_name} (
                                                    {linkedEmployee.employee_code})
                                                </div>
                                            )
                                        )}
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
                                        <div className="space-y-2">
                                            <Label>{t('reason')}</Label>
                                            <Textarea
                                                value={form.data.reason}
                                                onChange={(e) =>
                                                    form.setData('reason', e.target.value)
                                                }
                                                placeholder={t('reasonPlaceholder')}
                                                rows={3}
                                            />
                                            {form.errors.reason && (
                                                <p className="text-sm text-destructive">
                                                    {form.errors.reason}
                                                </p>
                                            )}
                                        </div>
                                        {form.errors.employee_id && (
                                            <p className="text-sm text-destructive">
                                                {form.errors.employee_id}
                                            </p>
                                        )}
                                        <Button type="submit" disabled={form.processing}>
                                            {t('submitLeaveRequest')}
                                        </Button>
                                    </form>
                                )}
                            </DialogContent>
                        </Dialog>
                    )}
                </div>

                {selfService && !linkedEmployee && (
                    <Card>
                        <CardContent className="p-4 text-sm text-muted-foreground">
                            {t('leaveNotLinked')}
                        </CardContent>
                    </Card>
                )}

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
                                    {!selfService && <TableHead>{t('employee')}</TableHead>}
                                    <TableHead>{t('leaveType')}</TableHead>
                                    <TableHead>{t('startDate')}</TableHead>
                                    <TableHead>{t('endDate')}</TableHead>
                                    <TableHead>{t('reason')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {leaves.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={selfService ? 6 : 7}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    leaves.data.map((row) => (
                                        <TableRow key={row.id}>
                                            {!selfService && (
                                                <TableCell>
                                                    {row.employee_name}
                                                    <span className="ml-1 text-xs text-muted-foreground">
                                                        {row.employee_code}
                                                    </span>
                                                </TableCell>
                                            )}
                                            <TableCell>{row.type}</TableCell>
                                            <TableCell>{row.start_date}</TableCell>
                                            <TableCell>{row.end_date}</TableCell>
                                            <TableCell className="max-w-[12rem] truncate text-sm text-muted-foreground">
                                                {row.reason || '—'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant="outline"
                                                    className={statusClass[row.status]}
                                                >
                                                    {row.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                {canCancel && row.status === 'pending' && (
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
