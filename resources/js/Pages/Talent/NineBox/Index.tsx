import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Textarea } from '@/Components/ui/textarea';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
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
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type AssessmentItem = {
    id: number;
    employee_id: number;
    employee_name: string | null;
    employee_code: string | null;
    period_year: number;
    performance_score: number;
    potential_score: number;
    box_label: string | null;
    notes: string | null;
};

type GridCell = {
    performance: number;
    potential: number;
    label: string;
    employees: AssessmentItem[];
};

const cellTone = (performance: number, potential: number): string => {
    const score = performance + potential;
    if (score >= 5) return 'bg-emerald-50 border-emerald-200 dark:bg-emerald-950/30';
    if (score >= 4) return 'bg-amber-50 border-amber-200 dark:bg-amber-950/30';
    return 'bg-rose-50 border-rose-200 dark:bg-rose-950/30';
};

export default function Index({
    items,
    grid,
    year,
    years,
    employees,
    summary,
    flash,
}: PageProps<{
    items: AssessmentItem[];
    grid: GridCell[][];
    year: number;
    years: number[];
    employees: Array<{ id: number; name: string }>;
    summary: { total: number; stars: number };
}>) {
    const { t } = useLanguage();
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editId, setEditId] = useState<number | null>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const { data, setData, post, put, processing, reset } = useForm({
        employee_id: '',
        period_year: String(year),
        performance_score: '2',
        potential_score: '2',
        notes: '',
    });

    const openCreate = () => {
        setEditId(null);
        reset();
        setData('period_year', String(year));
        setDialogOpen(true);
    };

    const openEdit = (item: AssessmentItem) => {
        setEditId(item.id);
        setData({
            employee_id: String(item.employee_id),
            period_year: String(item.period_year),
            performance_score: String(item.performance_score),
            potential_score: String(item.potential_score),
            notes: item.notes ?? '',
        });
        setDialogOpen(true);
    };

    const closeDialog = () => {
        setDialogOpen(false);
        setEditId(null);
        reset();
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editId) {
            put(route('succession.nine-box.update', editId), { onSuccess: closeDialog });
        } else {
            post(route('succession.nine-box.store'), { onSuccess: closeDialog });
        }
    };

    const destroy = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('succession.nine-box.destroy', id));
    };

    const changeYear = (value: string) => {
        router.get(route('succession.nine-box.index'), { year: value }, { preserveState: true });
    };

    return (
        <HrisLayout>
            <Head title={t('nineBox')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold text-foreground">{t('nineBox')}</h1>
                    <div className="flex flex-wrap items-center gap-2">
                        <Select value={String(year)} onValueChange={changeYear}>
                            <SelectTrigger className="w-28">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {years.map((y) => (
                                    <SelectItem key={y} value={String(y)}>
                                        {y}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>

                        <Dialog
                            open={dialogOpen}
                            onOpenChange={(open) => (open ? setDialogOpen(true) : closeDialog())}
                        >
                            <DialogTrigger asChild>
                                <Button onClick={openCreate}>
                                    <Plus className="mr-2 h-4 w-4" />
                                    {t('addAssessment')}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>
                                        {editId ? t('edit') : t('addAssessment')}
                                    </DialogTitle>
                                </DialogHeader>
                                <form onSubmit={submit} className="space-y-4">
                                    <div className="space-y-2">
                                        <Label>{t('employee')}</Label>
                                        <Select
                                            value={data.employee_id}
                                            onValueChange={(v) => setData('employee_id', v)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue placeholder={t('employee')} />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {employees.map((e) => (
                                                    <SelectItem key={e.id} value={String(e.id)}>
                                                        {e.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2">
                                        <Label>{t('year')}</Label>
                                        <Select
                                            value={data.period_year}
                                            onValueChange={(v) => setData('period_year', v)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {years.map((y) => (
                                                    <SelectItem key={y} value={String(y)}>
                                                        {y}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="grid grid-cols-2 gap-3">
                                        <div className="space-y-2">
                                            <Label>{t('performanceScore')}</Label>
                                            <Select
                                                value={data.performance_score}
                                                onValueChange={(v) => setData('performance_score', v)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {[1, 2, 3].map((n) => (
                                                        <SelectItem key={n} value={String(n)}>
                                                            {n}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                        <div className="space-y-2">
                                            <Label>{t('potentialScore')}</Label>
                                            <Select
                                                value={data.potential_score}
                                                onValueChange={(v) => setData('potential_score', v)}
                                            >
                                                <SelectTrigger>
                                                    <SelectValue />
                                                </SelectTrigger>
                                                <SelectContent>
                                                    {[1, 2, 3].map((n) => (
                                                        <SelectItem key={n} value={String(n)}>
                                                            {n}
                                                        </SelectItem>
                                                    ))}
                                                </SelectContent>
                                            </Select>
                                        </div>
                                    </div>
                                    <div className="space-y-2">
                                        <Label>{t('notes')}</Label>
                                        <Textarea
                                            value={data.notes}
                                            onChange={(e) => setData('notes', e.target.value)}
                                        />
                                    </div>
                                    <div className="flex justify-end gap-2">
                                        <Button type="button" variant="outline" onClick={closeDialog}>
                                            {t('cancel')}
                                        </Button>
                                        <Button type="submit" disabled={processing}>
                                            {t('save')}
                                        </Button>
                                    </div>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <div className="flex gap-2">
                    {/* Vertical axis label */}
                    <div className="flex items-center">
                        <span className="-rotate-90 whitespace-nowrap text-sm font-medium text-muted-foreground">
                            {t('potential')} →
                        </span>
                    </div>
                    <div className="flex-1 space-y-2">
                        <div className="grid grid-cols-3 gap-2">
                            {grid.flat().map((cell) => (
                                <Card
                                    key={`${cell.performance}-${cell.potential}`}
                                    className={`border ${cellTone(cell.performance, cell.potential)}`}
                                >
                                    <CardContent className="min-h-[120px] space-y-2 p-3">
                                        <p className="text-xs font-semibold uppercase tracking-wide text-muted-foreground">
                                            {cell.label}
                                        </p>
                                        <div className="space-y-1">
                                            {cell.employees.map((emp) => (
                                                <div
                                                    key={emp.id}
                                                    className="truncate rounded bg-background/70 px-2 py-1 text-xs"
                                                    title={emp.employee_name ?? ''}
                                                >
                                                    {emp.employee_name}
                                                </div>
                                            ))}
                                        </div>
                                    </CardContent>
                                </Card>
                            ))}
                        </div>
                        <p className="text-center text-sm font-medium text-muted-foreground">
                            {t('performance')} →
                        </p>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>
                            {t('assessments')} ({summary.total}) · {t('stars')}: {summary.stars}
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('performanceScore')}</TableHead>
                                    <TableHead>{t('potentialScore')}</TableHead>
                                    <TableHead>{t('box')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    items.map((item) => (
                                        <TableRow key={item.id}>
                                            <TableCell>{item.employee_name}</TableCell>
                                            <TableCell>{item.performance_score}</TableCell>
                                            <TableCell>{item.potential_score}</TableCell>
                                            <TableCell>{item.box_label}</TableCell>
                                            <TableCell>
                                                <div className="flex gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() => openEdit(item)}
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() => destroy(item.id)}
                                                    >
                                                        <Trash2 className="h-4 w-4 text-destructive" />
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
