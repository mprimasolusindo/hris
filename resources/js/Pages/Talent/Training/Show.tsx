import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
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

type TrainingData = {
    id: number;
    name: string;
    description: string | null;
    start_date: string | null;
    end_date: string | null;
    location: string | null;
    status: string;
};

type Participant = {
    id: number;
    name: string;
    employee_code: string;
    status: string;
};

export default function Show({
    training,
    participants,
    employees,
    enrollmentStatuses,
    flash,
}: PageProps<{
    training: TrainingData;
    participants: Participant[];
    employees: Array<{ id: number; name: string }>;
    enrollmentStatuses: string[];
}>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const assignForm = useForm({
        employee_id: String(employees[0]?.id ?? ''),
        status: enrollmentStatuses[0] ?? 'registered',
    });

    const assign: FormEventHandler = (e) => {
        e.preventDefault();
        if (!assignForm.data.employee_id) return;
        assignForm.post(route('training.assign', training.id), {
            onSuccess: () => assignForm.reset('employee_id'),
        });
    };

    const unassign = (employeeId: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('training.unassign', [training.id, employeeId]));
    };

    return (
        <HrisLayout>
            <Head title={training.name} />

            <div className="mx-auto max-w-4xl space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('training.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <h1 className="text-2xl font-bold text-foreground">{training.name}</h1>
                    <Badge variant="outline">{t((training.status as never)) || training.status}</Badge>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('details')}</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-3 sm:grid-cols-2">
                        <div>
                            <p className="text-sm text-muted-foreground">{t('startDate')}</p>
                            <p>{training.start_date}</p>
                        </div>
                        <div>
                            <p className="text-sm text-muted-foreground">{t('endDate')}</p>
                            <p>{training.end_date}</p>
                        </div>
                        <div>
                            <p className="text-sm text-muted-foreground">{t('location')}</p>
                            <p>{training.location || '-'}</p>
                        </div>
                        <div className="sm:col-span-2">
                            <p className="text-sm text-muted-foreground">{t('description')}</p>
                            <p>{training.description || '-'}</p>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('assignEmployee')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={assign} className="flex flex-wrap items-end gap-3">
                            <div className="min-w-[200px] flex-1 space-y-2">
                                <Label>{t('employee')}</Label>
                                <Select
                                    value={assignForm.data.employee_id}
                                    onValueChange={(v) => assignForm.setData('employee_id', v)}
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
                            <div className="min-w-[160px] space-y-2">
                                <Label>{t('status')}</Label>
                                <Select
                                    value={assignForm.data.status}
                                    onValueChange={(v) => assignForm.setData('status', v)}
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {enrollmentStatuses.map((s) => (
                                            <SelectItem key={s} value={s}>
                                                {t((s as never)) || s}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <Button
                                type="submit"
                                disabled={assignForm.processing || employees.length === 0}
                            >
                                {t('assignEmployee')}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('participants')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {participants.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={3}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    participants.map((p) => (
                                        <TableRow key={p.id}>
                                            <TableCell>
                                                {p.name}
                                                <span className="ml-2 text-xs text-muted-foreground">
                                                    {p.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {t((p.status as never)) || p.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell>
                                                <Button
                                                    variant="ghost"
                                                    size="icon"
                                                    type="button"
                                                    onClick={() => unassign(p.id)}
                                                >
                                                    <Trash2 className="h-4 w-4 text-destructive" />
                                                </Button>
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
