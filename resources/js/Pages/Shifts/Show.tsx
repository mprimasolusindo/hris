import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import { ArrowLeft } from 'lucide-react';

type AssignmentRow = {
    id: number;
    date: string;
    employee_name: string | null;
    employee_code: string | null;
};

export default function Show({
    shift,
    assignments,
}: PageProps<{
    shift: { id: number; name: string; start_time: string; end_time: string };
    assignments: AssignmentRow[];
}>) {
    const { t } = useLanguage();

    return (
        <HrisLayout>
            <Head title={shift.name} />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('shifts.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <h1 className="text-2xl font-bold text-foreground">{shift.name}</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('shiftDetail')}</CardTitle>
                    </CardHeader>
                    <CardContent className="grid gap-2 sm:grid-cols-2">
                        <p>
                            <span className="text-muted-foreground">{t('startTime')}:</span>{' '}
                            {shift.start_time}
                        </p>
                        <p>
                            <span className="text-muted-foreground">{t('endTime')}:</span>{' '}
                            {shift.end_time}
                        </p>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('assignedEmployees')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('date')}</TableHead>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('employeeCode')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {assignments.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={3}
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
                                            <TableCell>{row.employee_code}</TableCell>
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
