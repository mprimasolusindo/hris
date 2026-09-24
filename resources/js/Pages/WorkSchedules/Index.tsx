import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import type { Paginated } from '@/types/pagination';
import { PerPageSelect } from '@/Components/table/PerPageSelect';
import { TablePagination } from '@/Components/table/TablePagination';
import { FormEventHandler, useEffect, useState } from 'react';
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type ScheduleRow = {
    id: number;
    name: string;
    code: string;
    is_default: boolean;
    default_start_time: string | null;
    default_end_time: string | null;
    mon_working: boolean;
    tue_working: boolean;
    wed_working: boolean;
    thu_working: boolean;
    fri_working: boolean;
    sat_working: boolean;
    sun_working: boolean;
    working_days_label: string;
};

const emptyForm = {
    name: '',
    code: '',
    is_default: false,
    default_start_time: '08:00',
    default_end_time: '17:00',
    mon_working: true,
    tue_working: true,
    wed_working: true,
    thu_working: true,
    fri_working: true,
    sat_working: false,
    sun_working: false,
};

export default function Index({
    schedules,
    filters,
    flash,
}: PageProps<{ schedules: Paginated<ScheduleRow>; filters: { per_page: string } }>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
        if (flash?.error) toast.error(flash.error);
    }, [flash?.success, flash?.error]);

    const form = useForm({ ...emptyForm });

    const openCreate = () => {
        setEditingId(null);
        form.reset();
        form.setData({ ...emptyForm });
        form.clearErrors();
        setOpen(true);
    };

    const openEdit = (row: ScheduleRow) => {
        setEditingId(row.id);
        form.clearErrors();
        form.setData({
            name: row.name,
            code: row.code,
            is_default: row.is_default,
            default_start_time: row.default_start_time ?? '',
            default_end_time: row.default_end_time ?? '',
            mon_working: row.mon_working,
            tue_working: row.tue_working,
            wed_working: row.wed_working,
            thu_working: row.thu_working,
            fri_working: row.fri_working,
            sat_working: row.sat_working,
            sun_working: row.sun_working,
        });
        setOpen(true);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setOpen(false);
            setEditingId(null);
            form.reset();
        };
        if (editingId === null) {
            form.post(route('work-schedules.store'), { onSuccess });
        } else {
            form.put(route('work-schedules.update', editingId), { onSuccess });
        }
    };

    const remove = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('work-schedules.destroy', id), { preserveScroll: true });
    };

    const weekdayFields: Array<{
        key: keyof typeof emptyForm;
        label: string;
    }> = [
        { key: 'mon_working', label: t('mon') },
        { key: 'tue_working', label: t('tue') },
        { key: 'wed_working', label: t('wed') },
        { key: 'thu_working', label: t('thu') },
        { key: 'fri_working', label: t('fri') },
        { key: 'sat_working', label: t('sat') },
        { key: 'sun_working', label: t('sun') },
    ];

    return (
        <HrisLayout>
            <Head title={t('workSchedules')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">{t('workSchedules')}</h1>
                        <p className="text-sm text-muted-foreground">{t('workSchedulesSubtitle')}</p>
                    </div>
                    <Button onClick={openCreate}>
                        <Plus className="mr-2 h-4 w-4" />
                        {t('addWorkSchedule')}
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('code')}</TableHead>
                                    <TableHead>{t('name')}</TableHead>
                                    <TableHead>{t('workingDays')}</TableHead>
                                    <TableHead>{t('defaultHours')}</TableHead>
                                    <TableHead>{t('default')}</TableHead>
                                    <TableHead className="text-right">{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {schedules.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={6} className="py-8 text-center text-muted-foreground">
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    schedules.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="font-mono text-sm">{row.code}</TableCell>
                                            <TableCell>{row.name}</TableCell>
                                            <TableCell className="text-sm">{row.working_days_label}</TableCell>
                                            <TableCell className="tabular-nums text-sm">
                                                {row.default_start_time && row.default_end_time
                                                    ? `${row.default_start_time} – ${row.default_end_time}`
                                                    : '—'}
                                            </TableCell>
                                            <TableCell>
                                                {row.is_default ? (
                                                    <Badge>{t('default')}</Badge>
                                                ) : (
                                                    <span className="text-muted-foreground">—</span>
                                                )}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button size="icon" variant="ghost" aria-label={t('edit')} onClick={() => openEdit(row)}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                    <Button size="icon" variant="ghost" aria-label={t('delete')} onClick={() => remove(row.id)}>
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
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <PerPageSelect
                        value={filters.per_page}
                        onChange={(v) =>
                            router.get(route('work-schedules.index'), { per_page: v, page: 1 })
                        }
                    />
                    <TablePagination paginator={schedules} />
                </div>
            </div>

            <Dialog
                open={open}
                onOpenChange={(o) => {
                    setOpen(o);
                    if (!o) {
                        setEditingId(null);
                        form.reset();
                        form.clearErrors();
                    }
                }}
            >
                <DialogContent className="max-h-[90vh] overflow-y-auto sm:max-w-lg">
                    <DialogHeader>
                        <DialogTitle>
                            {editingId === null ? t('addWorkSchedule') : t('editWorkSchedule')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submit} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('code')}</Label>
                            <Input value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} required />
                            {form.errors.code && <p className="text-sm text-destructive">{form.errors.code}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('name')}</Label>
                            <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                            {form.errors.name && <p className="text-sm text-destructive">{form.errors.name}</p>}
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label>{t('startTime')}</Label>
                                <Input
                                    type="time"
                                    value={form.data.default_start_time}
                                    onChange={(e) => form.setData('default_start_time', e.target.value)}
                                />
                                {form.errors.default_start_time && (
                                    <p className="text-sm text-destructive">{form.errors.default_start_time}</p>
                                )}
                            </div>
                            <div className="space-y-2">
                                <Label>{t('endTime')}</Label>
                                <Input
                                    type="time"
                                    value={form.data.default_end_time}
                                    onChange={(e) => form.setData('default_end_time', e.target.value)}
                                />
                                {form.errors.default_end_time && (
                                    <p className="text-sm text-destructive">{form.errors.default_end_time}</p>
                                )}
                            </div>
                        </div>
                        <div>
                            <Label className="mb-2 block">{t('workingDays')}</Label>
                            <div className="flex flex-wrap gap-3">
                                {weekdayFields.map(({ key, label }) => (
                                    <label key={key} className="flex items-center gap-2 text-sm">
                                        <Checkbox
                                            checked={!!form.data[key]}
                                            onCheckedChange={(v) => form.setData(key, !!v)}
                                        />
                                        {label}
                                    </label>
                                ))}
                            </div>
                        </div>
                        <div className="flex items-center gap-2">
                            <Checkbox
                                checked={form.data.is_default}
                                onCheckedChange={(v) => form.setData('is_default', !!v)}
                            />
                            <Label>{t('setAsDefault')}</Label>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={form.processing}>
                                {t('save')}
                            </Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </HrisLayout>
    );
}
