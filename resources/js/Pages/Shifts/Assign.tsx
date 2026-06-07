import HrisLayout from '@/Layouts/HrisLayout';
import { Head, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { PageProps } from '@/types';
import { FormEventHandler, useEffect, useState } from 'react';
import { toast } from 'sonner';

export default function Assign({
    shifts,
    employees,
    selectedShiftId,
    defaultDate,
    flash,
}: PageProps<{
    shifts: Array<{ id: number; name: string }>;
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
    selectedShiftId: string | null;
    defaultDate: string;
    flash?: { success?: string | null };
}>) {
    const { t } = useLanguage();
    const [picked, setPicked] = useState<number[]>([]);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const { data, setData, post, processing, errors } = useForm({
        shift_id: String(selectedShiftId ?? shifts[0]?.id ?? ''),
        date: defaultDate,
        employee_ids: [] as number[],
    });

    const toggle = (id: number, checked: boolean) => {
        setPicked((prev) => {
            const next = checked ? [...prev, id] : prev.filter((x) => x !== id);
            setData('employee_ids', next);
            return next;
        });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (picked.length === 0) {
            toast.error(t('selectEmployees'));
            return;
        }
        post(route('shifts.assign.store'));
    };

    return (
        <HrisLayout>
            <Head title={t('bulkAssign')} />

            <form onSubmit={submit} className="mx-auto max-w-2xl space-y-6">
                <h1 className="text-2xl font-bold text-foreground">{t('bulkAssign')}</h1>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('shiftAssignment')}</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-4">
                        <div className="space-y-2">
                            <Label>{t('shifts')}</Label>
                            <Select
                                value={data.shift_id}
                                onValueChange={(v) => setData('shift_id', v)}
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {shifts.map((s) => (
                                        <SelectItem key={s.id} value={String(s.id)}>
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {errors.shift_id && (
                                <p className="text-sm text-destructive">{errors.shift_id}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('date')}</Label>
                            <Input
                                type="date"
                                value={data.date}
                                onChange={(e) => setData('date', e.target.value)}
                                required
                            />
                            {errors.date && (
                                <p className="text-sm text-destructive">{errors.date}</p>
                            )}
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('employees')}</CardTitle>
                    </CardHeader>
                    <CardContent className="max-h-96 space-y-3 overflow-y-auto">
                        {employees.map((emp) => (
                            <div key={emp.id} className="flex items-center gap-2">
                                <Checkbox
                                    id={`emp-${emp.id}`}
                                    checked={picked.includes(emp.id)}
                                    onCheckedChange={(c) => toggle(emp.id, Boolean(c))}
                                />
                                <Label htmlFor={`emp-${emp.id}`} className="font-normal">
                                    {emp.full_name} ({emp.employee_code})
                                </Label>
                            </div>
                        ))}
                    </CardContent>
                </Card>

                <Button type="submit" disabled={processing}>
                    {t('save')}
                </Button>
            </form>
        </HrisLayout>
    );
}
