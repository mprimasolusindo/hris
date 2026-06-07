import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import { ChevronLeft, ChevronRight, Users } from 'lucide-react';

type AssignmentRow = {
    id: number;
    date: string;
    employee_name: string | null;
    shift_name: string | null;
    start_time: string;
    end_time: string;
};

export default function Calendar({
    weekStart,
    assignments,
}: PageProps<{ weekStart: string; assignments: AssignmentRow[] }>) {
    const { t } = useLanguage();

    const weekDate = new Date(weekStart + 'T00:00:00');
    const prevWeek = new Date(weekDate);
    prevWeek.setDate(prevWeek.getDate() - 7);
    const nextWeek = new Date(weekDate);
    nextWeek.setDate(nextWeek.getDate() + 7);

    const toIso = (d: Date) => d.toISOString().slice(0, 10);

    const goWeek = (iso: string) => {
        router.get(route('shifts.calendar'), { week: iso });
    };

    return (
        <HrisLayout>
            <Head title={t('calendarView')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold text-foreground">{t('calendarView')}</h1>
                    <Button variant="outline" asChild>
                        <Link href={route('shifts.assign')}>
                            <Users className="mr-2 h-4 w-4" />
                            {t('bulkAssign')}
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap items-end gap-4 p-4">
                        <Button
                            variant="outline"
                            size="icon"
                            type="button"
                            onClick={() => goWeek(toIso(prevWeek))}
                        >
                            <ChevronLeft className="h-4 w-4" />
                        </Button>
                        <div className="space-y-2">
                            <Label>{t('startDate')}</Label>
                            <Input
                                type="date"
                                value={weekStart}
                                onChange={(e) => goWeek(e.target.value)}
                                className="w-44"
                            />
                        </div>
                        <Button
                            variant="outline"
                            size="icon"
                            type="button"
                            onClick={() => goWeek(toIso(nextWeek))}
                        >
                            <ChevronRight className="h-4 w-4" />
                        </Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('date')}</TableHead>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('shifts')}</TableHead>
                                    <TableHead>{t('startTime')}</TableHead>
                                    <TableHead>{t('endTime')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {assignments.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    assignments.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.date}</TableCell>
                                            <TableCell>{row.employee_name}</TableCell>
                                            <TableCell>{row.shift_name}</TableCell>
                                            <TableCell>{row.start_time}</TableCell>
                                            <TableCell>{row.end_time}</TableCell>
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
