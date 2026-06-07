import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Checkbox } from '@/Components/ui/checkbox';
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
import { toast } from 'sonner';

type PayrollRow = {
    id: number;
    employee_name: string;
    employee_code: string;
    company_name: string | null;
    site_name: string | null;
    period_month: number;
    period_year: number;
    gross_salary: string;
    total_deduction: string;
    net_salary: string;
    status: string;
};

export default function Index({
    payrolls,
    filters,
    summary,
    employees,
    companies,
    sites,
    flash,
}: PageProps<{
    payrolls: { data: PayrollRow[] };
    filters: Record<string, string | number>;
    summary: Record<string, number>;
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
    companies: Array<{ id: number; name: string }>;
    sites: Array<{ id: number; name: string }>;
}>) {
    const { t } = useLanguage();
    const [selected, setSelected] = useState<number[]>([]);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const generateForm = useForm({
        employee_id: String(employees[0]?.id ?? ''),
        period_month: Number(filters.month === 'all' ? new Date().getMonth() + 1 : filters.month),
        period_year: Number(filters.year),
        base_salary: '',
    });

    const bulkForm = useForm({
        payroll_ids: [] as number[],
        action: 'reviewed',
        approval_notes: '',
        month: String(filters.month),
        year: String(filters.year),
        employee_id: String(filters.employee_id || ''),
        company_id: String(filters.company_id || ''),
        site_id: String(filters.site_id || ''),
        status: String(filters.status),
    });

    const applyFilters = (patch: Record<string, string | number | undefined>) => {
        router.get(route('payroll.index'), { ...filters, ...patch });
    };

    const submitGenerate: FormEventHandler = (e) => {
        e.preventDefault();
        generateForm.post(route('payroll.store'));
    };

    const submitBulk = () => {
        if (selected.length === 0) {
            toast.error('Select at least one payroll row.');
            return;
        }
        if (
            !window.confirm(
                `Apply "${bulkForm.data.action}" to ${selected.length} row(s)?`,
            )
        ) {
            return;
        }
        bulkForm.setData('payroll_ids', selected);
        bulkForm.post(route('payroll.bulk-update'));
    };

    const toggleRow = (id: number, checked: boolean) => {
        setSelected((prev) =>
            checked ? [...prev, id] : prev.filter((x) => x !== id),
        );
    };

    const formatMoney = (v: string) =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(Number(v));

    return (
        <HrisLayout>
            <Head title={t('payroll')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <h1 className="text-2xl font-bold text-foreground">
                        {t('payroll')}
                    </h1>
                    <Button variant="outline" asChild>
                        <a
                            href={route('payroll.index', {
                                ...filters,
                                export: 'csv',
                            })}
                        >
                            Export CSV
                        </a>
                    </Button>
                </div>

                <div className="grid grid-cols-2 gap-4 lg:grid-cols-4">
                    {[
                        ['Draft', summary.draft],
                        ['Generated', summary.generated],
                        ['Paid', summary.paid],
                        ['Total net', formatMoney(String(summary.total_amount))],
                    ].map(([label, value]) => (
                        <Card key={String(label)} className="shadow-sm">
                            <CardContent className="p-4">
                                <p className="text-sm text-muted-foreground">
                                    {label}
                                </p>
                                <p className="text-2xl font-bold">{value}</p>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">Filters</CardTitle>
                    </CardHeader>
                    <CardContent className="flex flex-wrap gap-4">
                        <Select
                            value={String(filters.month)}
                            onValueChange={(v) => applyFilters({ month: v })}
                        >
                            <SelectTrigger className="w-28">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {Array.from({ length: 12 }, (_, i) => i + 1).map(
                                    (m) => (
                                        <SelectItem key={m} value={String(m)}>
                                            {m}
                                        </SelectItem>
                                    ),
                                )}
                            </SelectContent>
                        </Select>
                        <Input
                            type="number"
                            className="w-28"
                            value={String(filters.year)}
                            onChange={(e) =>
                                applyFilters({ year: e.target.value })
                            }
                        />
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">Generate payroll</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form
                            onSubmit={submitGenerate}
                            className="flex flex-wrap gap-4"
                        >
                            <Select
                                value={generateForm.data.employee_id}
                                onValueChange={(v) =>
                                    generateForm.setData('employee_id', v)
                                }
                            >
                                <SelectTrigger className="w-56">
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
                            <Input
                                type="number"
                                min={1}
                                max={12}
                                className="w-24"
                                value={generateForm.data.period_month}
                                onChange={(e) =>
                                    generateForm.setData(
                                        'period_month',
                                        Number(e.target.value),
                                    )
                                }
                            />
                            <Input
                                type="number"
                                className="w-28"
                                value={generateForm.data.period_year}
                                onChange={(e) =>
                                    generateForm.setData(
                                        'period_year',
                                        Number(e.target.value),
                                    )
                                }
                            />
                            <Input
                                type="number"
                                placeholder="Base salary"
                                className="w-40"
                                value={generateForm.data.base_salary}
                                onChange={(e) =>
                                    generateForm.setData(
                                        'base_salary',
                                        e.target.value,
                                    )
                                }
                                required
                            />
                            <Button type="submit" disabled={generateForm.processing}>
                                Generate
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardHeader className="flex flex-row flex-wrap items-center justify-between gap-4">
                        <CardTitle className="text-base">Payroll runs</CardTitle>
                        <div className="flex flex-wrap gap-2">
                            <Select
                                value={bulkForm.data.action}
                                onValueChange={(v) =>
                                    bulkForm.setData('action', v)
                                }
                            >
                                <SelectTrigger className="w-44">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="reviewed">Review</SelectItem>
                                    <SelectItem value="approved">Approve</SelectItem>
                                    <SelectItem value="paid">Paid</SelectItem>
                                    <SelectItem value="draft">Draft</SelectItem>
                                </SelectContent>
                            </Select>
                            <Button type="button" onClick={submitBulk}>
                                Bulk action
                            </Button>
                        </div>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead />
                                    <TableHead>ID</TableHead>
                                    <TableHead>{t('employees')}</TableHead>
                                    <TableHead>Period</TableHead>
                                    <TableHead>Net</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead />
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {payrolls.data.map((row) => (
                                    <TableRow key={row.id}>
                                        <TableCell>
                                            <Checkbox
                                                checked={selected.includes(
                                                    row.id,
                                                )}
                                                onCheckedChange={(c) =>
                                                    toggleRow(row.id, !!c)
                                                }
                                            />
                                        </TableCell>
                                        <TableCell>{row.id}</TableCell>
                                        <TableCell>{row.employee_name}</TableCell>
                                        <TableCell>
                                            {String(row.period_month).padStart(2, '0')}/
                                            {row.period_year}
                                        </TableCell>
                                        <TableCell>
                                            {formatMoney(row.net_salary)}
                                        </TableCell>
                                        <TableCell>
                                            <Badge variant="secondary">
                                                {row.status}
                                            </Badge>
                                        </TableCell>
                                        <TableCell>
                                            <Button variant="link" asChild>
                                                <Link
                                                    href={route(
                                                        'payroll.show',
                                                        row.id,
                                                    )}
                                                >
                                                    {t('view')}
                                                </Link>
                                            </Button>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
