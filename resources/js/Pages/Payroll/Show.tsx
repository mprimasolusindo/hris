import { PayslipPrintable } from '@/Components/payroll/PayslipPrintable';
import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Printer } from 'lucide-react';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';

export default function Show({
    payroll,
    items,
    attendanceSummary,
}: PageProps<{
    payroll: Record<string, string | number | null>;
    items: Array<{ id: number; type: string; name: string; amount: string }>;
    attendanceSummary: Record<string, number>;
}>) {
    const { t } = useLanguage();

    return (
        <HrisLayout>
            <Head title={`Payroll #${payroll.id}`} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">
                            {payroll.employee_name}
                        </h1>
                        <p className="text-sm text-muted-foreground">
                            {String(payroll.period_month).padStart(2, '0')}/
                            {payroll.period_year} · {payroll.employee_code}
                        </p>
                    </div>
                    <div className="flex gap-2">
                        <Button
                            type="button"
                            variant="outline"
                            onClick={() => window.print()}
                        >
                            <Printer className="mr-2 h-4 w-4" />
                            {t('printPayslip')}
                        </Button>
                        <Button variant="outline" asChild>
                            <Link href={route('payroll.index')}>{t('cancel')}</Link>
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-4">
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                Gross
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="font-bold">
                            {payroll.gross_salary}
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                Deduction
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="font-bold">
                            {payroll.total_deduction}
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                Net
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="font-bold">
                            {payroll.net_salary}
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                {t('status')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <Badge>{String(payroll.status)}</Badge>
                        </CardContent>
                    </Card>
                </div>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">Line items</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>Type</TableHead>
                                    <TableHead>Name</TableHead>
                                    <TableHead>Amount</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.map((item) => (
                                    <TableRow key={item.id}>
                                        <TableCell>{item.type}</TableCell>
                                        <TableCell>{item.name}</TableCell>
                                        <TableCell>{item.amount}</TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">
                            {t('attendance')} summary
                        </CardTitle>
                    </CardHeader>
                    <CardContent className="grid grid-cols-3 gap-4 text-center">
                        <div>
                            <p className="text-2xl font-bold">
                                {attendanceSummary.days}
                            </p>
                            <p className="text-xs text-muted-foreground">Days</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold">
                                {attendanceSummary.leave_days}
                            </p>
                            <p className="text-xs text-muted-foreground">Leave</p>
                        </div>
                        <div>
                            <p className="text-2xl font-bold">
                                {attendanceSummary.overtime_hours}
                            </p>
                            <p className="text-xs text-muted-foreground">OT hrs</p>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <PayslipPrintable
                payroll={payroll as Parameters<typeof PayslipPrintable>[0]['payroll']}
                items={items}
            />
        </HrisLayout>
    );
}
