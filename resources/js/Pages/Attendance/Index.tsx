import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { PageProps } from '@/types';
import { FormEventHandler, useEffect, useState } from 'react';
import { Pencil, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type AttendanceRow = {
    id: number;
    employee_name: string | null;
    employee_code: string | null;
    site_id: number | null;
    site_name: string | null;
    clock_in: string | null;
    clock_out: string | null;
    status: string;
};

function toLocalInput(value: string | null): string {
    if (!value) return '';
    return value.replace(' ', 'T').slice(0, 16);
}

export default function Index({
    attendances,
    filters,
    summary,
    employees,
    sites,
    statusOptions,
    flash,
}: PageProps<{
    attendances: { data: AttendanceRow[] };
    filters: { date: string; site_id: string; employee_id: string };
    summary: { present: number; late: number; absent: number };
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
    sites: Array<{ id: number; name: string }>;
    statusOptions: string[];
}>) {
    const { t } = useLanguage();
    const [editing, setEditing] = useState<AttendanceRow | null>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const captureForm = useForm({
        employee_id: String(employees[0]?.id ?? ''),
        site_id: '',
        clock_in: '',
        clock_out: '',
        status: 'present',
        filter_date: filters.date,
    });

    const editForm = useForm({
        site_id: '',
        clock_in: '',
        clock_out: '',
        status: 'present',
    });

    const submitCapture: FormEventHandler = (e) => {
        e.preventDefault();
        captureForm.post(route('attendance.store'));
    };

    const openEdit = (row: AttendanceRow) => {
        setEditing(row);
        editForm.clearErrors();
        editForm.setData({
            site_id: row.site_id ? String(row.site_id) : '',
            clock_in: toLocalInput(row.clock_in),
            clock_out: toLocalInput(row.clock_out),
            status: row.status,
        });
    };

    const submitEdit: FormEventHandler = (e) => {
        e.preventDefault();
        if (!editing) return;
        editForm.transform((data) => ({
            ...data,
            site_id: data.site_id || null,
            clock_out: data.clock_out || null,
        }));
        editForm.put(route('attendance.update', editing.id), {
            preserveScroll: true,
            onSuccess: () => setEditing(null),
        });
    };

    const removeRow = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('attendance.destroy', id), { preserveScroll: true });
    };

    const applyFilters = (patch: Partial<typeof filters>) => {
        const next = { ...filters, ...patch };
        router.get(route('attendance.index'), {
            date: next.date,
            site_id: next.site_id || undefined,
            employee_id: next.employee_id || undefined,
        });
    };

    return (
        <HrisLayout>
            <Head title={t('attendance')} />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-foreground">
                    {t('attendance')}
                </h1>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Present</p>
                            <p className="text-2xl font-bold">{summary.present}</p>
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Late</p>
                            <p className="text-2xl font-bold">{summary.late}</p>
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardContent className="p-4">
                            <p className="text-sm text-muted-foreground">Absent</p>
                            <p className="text-2xl font-bold">{summary.absent}</p>
                        </CardContent>
                    </Card>
                </div>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">Filters</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-wrap gap-4">
                        <div className="space-y-2">
                            <Label>Date</Label>
                            <Input
                                type="date"
                                value={filters.date}
                                onChange={(e) =>
                                    applyFilters({ date: e.target.value })
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>{t('site')}</Label>
                            <Select
                                value={filters.site_id || 'all'}
                                onValueChange={(v) =>
                                    applyFilters({
                                        site_id: v === 'all' ? '' : v,
                                    })
                                }
                            >
                                <SelectTrigger className="w-48">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('all')}</SelectItem>
                                    {sites.map((s) => (
                                        <SelectItem
                                            key={s.id}
                                            value={String(s.id)}
                                        >
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">Capture attendance</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            onSubmit={submitCapture}
                            className="grid gap-4 md:grid-cols-2 lg:grid-cols-3"
                        >
                            <div className="space-y-2">
                                <Label>{t('employees')}</Label>
                                <Select
                                    value={captureForm.data.employee_id}
                                    onValueChange={(v) =>
                                        captureForm.setData('employee_id', v)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {employees.map((e) => (
                                            <SelectItem
                                                key={e.id}
                                                value={String(e.id)}
                                            >
                                                {e.employee_code} — {e.full_name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label>Clock in</Label>
                                <Input
                                    type="datetime-local"
                                    value={captureForm.data.clock_in}
                                    onChange={(e) =>
                                        captureForm.setData(
                                            'clock_in',
                                            e.target.value,
                                        )
                                    }
                                    required
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>{t('status')}</Label>
                                <Select
                                    value={captureForm.data.status}
                                    onValueChange={(v) =>
                                        captureForm.setData('status', v)
                                    }
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
                            <div className="flex items-end">
                                <Button
                                    type="submit"
                                    disabled={captureForm.processing}
                                >
                                    {t('save')}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employees')}</TableHead>
                                    <TableHead>{t('site')}</TableHead>
                                    <TableHead>{t('clockIn')}</TableHead>
                                    <TableHead>{t('clockOut')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead className="text-right">{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {attendances.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    attendances.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.employee_name}
                                            </TableCell>
                                            <TableCell>
                                                {row.site_name ?? '-'}
                                            </TableCell>
                                            <TableCell>{row.clock_in}</TableCell>
                                            <TableCell>
                                                {row.clock_out ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">
                                                    {row.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button
                                                        size="icon"
                                                        variant="ghost"
                                                        aria-label={t('edit')}
                                                        onClick={() => openEdit(row)}
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        size="icon"
                                                        variant="ghost"
                                                        aria-label={t('delete')}
                                                        onClick={() => removeRow(row.id)}
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

            <Dialog open={editing !== null} onOpenChange={(open) => !open && setEditing(null)}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('editAttendance')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEdit} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('site')}</Label>
                            <Select
                                value={editForm.data.site_id || '_none'}
                                onValueChange={(v) =>
                                    editForm.setData('site_id', v === '_none' ? '' : v)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue placeholder="—" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_none">—</SelectItem>
                                    {sites.map((s) => (
                                        <SelectItem key={s.id} value={String(s.id)}>
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label>{t('clockIn')}</Label>
                                <Input
                                    type="datetime-local"
                                    value={editForm.data.clock_in}
                                    onChange={(e) => editForm.setData('clock_in', e.target.value)}
                                    required
                                />
                                {editForm.errors.clock_in && (
                                    <p className="text-sm text-destructive">{editForm.errors.clock_in}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label>{t('clockOut')}</Label>
                                <Input
                                    type="datetime-local"
                                    value={editForm.data.clock_out}
                                    onChange={(e) => editForm.setData('clock_out', e.target.value)}
                                />
                                {editForm.errors.clock_out && (
                                    <p className="text-sm text-destructive">{editForm.errors.clock_out}</p>
                                )}
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('status')}</Label>
                            <Select
                                value={editForm.data.status}
                                onValueChange={(v) => editForm.setData('status', v)}
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
                        <DialogFooter>
                            <Button type="submit" disabled={editForm.processing}>
                                {t('save')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </HrisLayout>
    );
}
