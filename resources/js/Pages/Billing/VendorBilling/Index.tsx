import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
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
import { FormEventHandler, useEffect, useState } from 'react';
import { FileText } from 'lucide-react';
import { toast } from 'sonner';

type BillingLine = {
    vendor_id: number;
    vendor_name: string;
    headcount: number;
    hours: number;
    suggested_amount: number;
};

type Invoice = {
    id: number;
    invoice_number: string;
    vendor_id: number;
    vendor_name: string | null;
    period_start: string | null;
    period_end: string | null;
    amount: number;
    status: string;
    paid_at: string | null;
};

const statusClass: Record<string, string> = {
    paid: 'bg-green-500/10 text-green-700 border-green-500/30',
    issued: 'bg-blue-500/10 text-blue-700 border-blue-500/30',
    draft: 'bg-muted text-muted-foreground border-border',
    overdue: 'bg-destructive/10 text-destructive border-destructive/30',
    cancelled: 'bg-muted text-muted-foreground border-border',
};

function formatIdr(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

export default function Index({
    lines,
    invoices,
    filters,
    period_label,
    vendors,
    flash,
}: PageProps<{
    lines: BillingLine[];
    invoices: Invoice[];
    filters: { month: number; year: number };
    period_label: string;
    vendors: Array<{ id: number; name: string }>;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const periodStart = `${filters.year}-${String(filters.month).padStart(2, '0')}-01`;
    const periodEnd = new Date(filters.year, filters.month, 0)
        .toISOString()
        .slice(0, 10);

    const form = useForm({
        vendor_id: String(vendors[0]?.id ?? ''),
        period_start: periodStart,
        period_end: periodEnd,
        amount: '0',
        status: 'issued',
    });

    const apply = (patch: Partial<typeof filters>) => {
        router.get(route('vendor-billing.index'), { ...filters, ...patch });
    };

    const openGenerate = (line?: BillingLine) => {
        form.setData({
            vendor_id: String(line?.vendor_id ?? vendors[0]?.id ?? ''),
            period_start: periodStart,
            period_end: periodEnd,
            amount: String(line?.suggested_amount ?? 0),
            status: 'issued',
        });
        setOpen(true);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('vendor-billing.store'), {
            onSuccess: () => {
                setOpen(false);
                form.reset();
            },
        });
    };

    const markPaid = (invoice: Invoice) => {
        router.patch(route('vendor-billing.mark-paid', invoice.id));
    };

    return (
        <HrisLayout>
            <Head title={t('vendorBilling')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">
                            {t('vendorBilling')}
                        </h1>
                        <p className="mt-1 text-sm text-muted-foreground">
                            {period_label}
                        </p>
                        <p className="mt-1 text-xs text-muted-foreground">
                            {t('billingDisclaimer')}
                        </p>
                    </div>
                    <Button onClick={() => openGenerate()}>
                        <FileText className="mr-2 h-4 w-4" />
                        {t('generateInvoice')}
                    </Button>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap gap-4 p-4">
                        <div className="space-y-2">
                            <Label>{t('month')}</Label>
                            <Input
                                type="number"
                                min={1}
                                max={12}
                                className="w-24"
                                value={filters.month}
                                onChange={(e) =>
                                    apply({ month: Number(e.target.value) })
                                }
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>{t('year')}</Label>
                            <Input
                                type="number"
                                className="w-28"
                                value={filters.year}
                                onChange={(e) =>
                                    apply({ year: Number(e.target.value) })
                                }
                            />
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('billingRuns')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('vendors')}</TableHead>
                                    <TableHead className="text-right">
                                        {t('headcount')}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {t('hours')}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {t('suggestedAmount')}
                                    </TableHead>
                                    <TableHead className="text-right">
                                        {t('actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {lines.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={5}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    lines.map((row) => (
                                        <TableRow key={row.vendor_id}>
                                            <TableCell>
                                                {row.vendor_name}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {row.headcount}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {row.hours}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {formatIdr(row.suggested_amount)}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() =>
                                                        openGenerate(row)
                                                    }
                                                >
                                                    {t('generateInvoice')}
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('invoices')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('invoiceNumber')}</TableHead>
                                    <TableHead>{t('vendors')}</TableHead>
                                    <TableHead>{t('period')}</TableHead>
                                    <TableHead className="text-right">
                                        {t('amount')}
                                    </TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead className="text-right">
                                        {t('actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {invoices.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    invoices.map((invoice) => (
                                        <TableRow key={invoice.id}>
                                            <TableCell className="font-mono text-xs">
                                                {invoice.invoice_number}
                                            </TableCell>
                                            <TableCell>
                                                {invoice.vendor_name}
                                            </TableCell>
                                            <TableCell className="text-xs text-muted-foreground">
                                                {invoice.period_start} —{' '}
                                                {invoice.period_end}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {formatIdr(invoice.amount)}
                                            </TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant="outline"
                                                    className={
                                                        statusClass[
                                                            invoice.status
                                                        ]
                                                    }
                                                >
                                                    {invoice.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {invoice.status !== 'paid' &&
                                                    invoice.status !==
                                                        'cancelled' && (
                                                        <Button
                                                            variant="outline"
                                                            size="sm"
                                                            onClick={() =>
                                                                markPaid(invoice)
                                                            }
                                                        >
                                                            {t('markAsPaid')}
                                                        </Button>
                                                    )}
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>

            <Dialog open={open} onOpenChange={setOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('generateInvoice')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label>{t('vendors')}</Label>
                            <Select
                                value={form.data.vendor_id}
                                onValueChange={(v) =>
                                    form.setData('vendor_id', v)
                                }
                            >
                                <SelectTrigger>
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    {vendors.map((v) => (
                                        <SelectItem
                                            key={v.id}
                                            value={String(v.id)}
                                        >
                                            {v.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {form.errors.vendor_id && (
                                <p className="text-sm text-destructive">
                                    {form.errors.vendor_id}
                                </p>
                            )}
                        </div>
                        <div className="grid grid-cols-2 gap-4">
                            <div className="space-y-2">
                                <Label>{t('startDate')}</Label>
                                <Input
                                    type="date"
                                    value={form.data.period_start}
                                    onChange={(e) =>
                                        form.setData(
                                            'period_start',
                                            e.target.value,
                                        )
                                    }
                                />
                            </div>
                            <div className="space-y-2">
                                <Label>{t('endDate')}</Label>
                                <Input
                                    type="date"
                                    value={form.data.period_end}
                                    onChange={(e) =>
                                        form.setData(
                                            'period_end',
                                            e.target.value,
                                        )
                                    }
                                />
                            </div>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('amount')}</Label>
                            <Input
                                type="number"
                                min={0}
                                value={form.data.amount}
                                onChange={(e) =>
                                    form.setData('amount', e.target.value)
                                }
                            />
                            {form.errors.amount && (
                                <p className="text-sm text-destructive">
                                    {form.errors.amount}
                                </p>
                            )}
                        </div>
                        <div className="flex justify-end gap-2">
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => setOpen(false)}
                            >
                                {t('cancel')}
                            </Button>
                            <Button type="submit" disabled={form.processing}>
                                {t('save')}
                            </Button>
                        </div>
                    </form>
                </DialogContent>
            </Dialog>
        </HrisLayout>
    );
}
