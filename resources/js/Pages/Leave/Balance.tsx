import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
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

type TypeBalance = {
    entitlement: number;
    used: number;
    remaining: number | null;
};

type BalanceRow = {
    employee_id: number;
    employee_name: string;
    employee_code: string;
    year: number;
    total_entitlement: number;
    total_used: number;
    total_remaining: number;
    by_type: Record<string, TypeBalance>;
};

export default function Balance({
    year,
    balances,
}: PageProps<{ year: number; balances: BalanceRow[] }>) {
    const { t } = useLanguage();

    return (
        <HrisLayout>
            <Head title={t('leaveBalance')} />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-foreground">{t('leaveBalance')}</h1>

                <Card>
                    <CardContent className="p-4">
                        <div className="space-y-2">
                            <Label>{t('year')}</Label>
                            <Input
                                type="number"
                                className="w-32"
                                value={year}
                                onChange={(e) =>
                                    router.get(route('leave.balance.index'), {
                                        year: e.target.value,
                                    })
                                }
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('employeeCode')}</TableHead>
                                    <TableHead>{t('entitled')}</TableHead>
                                    <TableHead>{t('used')}</TableHead>
                                    <TableHead>{t('remaining')}</TableHead>
                                    <TableHead>{t('leaveType')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {balances.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    balances.map((row) => (
                                        <TableRow key={row.employee_id}>
                                            <TableCell>{row.employee_name}</TableCell>
                                            <TableCell>{row.employee_code}</TableCell>
                                            <TableCell>{row.total_entitlement}</TableCell>
                                            <TableCell>{row.total_used}</TableCell>
                                            <TableCell>{row.total_remaining}</TableCell>
                                            <TableCell className="text-sm text-muted-foreground">
                                                {Object.entries(row.by_type)
                                                    .map(
                                                        ([k, v]) =>
                                                            `${k}: ${v.used}/${v.entitlement || '∞'}`,
                                                    )
                                                    .join(' · ') || '-'}
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
