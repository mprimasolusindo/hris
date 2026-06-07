import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
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
import { Plus, Pencil, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type PaymentRow = {
    id: number;
    tenant_id: number;
    tenant_name: string | null;
    amount: number;
    method: string | null;
    status: string;
    paid_at: string | null;
};

const STATUSES = ['pending', 'paid', 'failed', 'refunded'];
const METHODS = ['transfer', 'virtual_account', 'credit_card'];

function formatIdr(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

export default function Index({
    payments,
    tenants,
    flash,
}: PageProps<{
    payments: PaymentRow[];
    tenants: Array<{ id: number; name: string }>;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);
    const [editId, setEditId] = useState<number | null>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        tenant_id: String(tenants[0]?.id ?? ''),
        amount: '0',
        method: 'transfer',
        status: 'pending',
    });

    const openCreate = () => {
        setEditId(null);
        form.setData({
            tenant_id: String(tenants[0]?.id ?? ''),
            amount: '0',
            method: 'transfer',
            status: 'pending',
        });
        setOpen(true);
    };

    const openEdit = (row: PaymentRow) => {
        setEditId(row.id);
        form.setData({
            tenant_id: String(row.tenant_id),
            amount: String(row.amount),
            method: row.method ?? 'transfer',
            status: row.status,
        });
        setOpen(true);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setOpen(false);
            setEditId(null);
            form.reset();
        };
        if (editId) {
            form.put(route('admin.saas.payments.update', editId), {
                onSuccess,
            });
        } else {
            form.post(route('admin.saas.payments.store'), { onSuccess });
        }
    };

    const remove = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('admin.saas.payments.destroy', id));
    };

    return (
        <HrisLayout>
            <Head title={t('payments')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">
                        {t('payments')}
                    </h1>
                    <Dialog
                        open={open}
                        onOpenChange={(o) => {
                            setOpen(o);
                            if (!o) setEditId(null);
                        }}
                    >
                        <DialogTrigger asChild>
                            <Button onClick={openCreate}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('recordPayment')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    {editId ? t('edit') : t('recordPayment')}
                                </DialogTitle>
                            </DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label>{t('tenantName')}</Label>
                                    <Select
                                        value={form.data.tenant_id}
                                        onValueChange={(v) =>
                                            form.setData('tenant_id', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {tenants.map((tn) => (
                                                <SelectItem
                                                    key={tn.id}
                                                    value={String(tn.id)}
                                                >
                                                    {tn.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('amount')}</Label>
                                    <Input
                                        type="number"
                                        min={0}
                                        value={form.data.amount}
                                        onChange={(e) =>
                                            form.setData(
                                                'amount',
                                                e.target.value,
                                            )
                                        }
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('paymentMethod')}</Label>
                                    <Select
                                        value={form.data.method}
                                        onValueChange={(v) =>
                                            form.setData('method', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {METHODS.map((m) => (
                                                <SelectItem key={m} value={m}>
                                                    {m}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('status')}</Label>
                                    <Select
                                        value={form.data.status}
                                        onValueChange={(v) =>
                                            form.setData('status', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {STATUSES.map((s) => (
                                                <SelectItem key={s} value={s}>
                                                    {s}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={() => setOpen(false)}
                                    >
                                        {t('cancel')}
                                    </Button>
                                    <Button
                                        type="submit"
                                        disabled={form.processing}
                                    >
                                        {t('save')}
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('tenantName')}</TableHead>
                                    <TableHead className="text-right">
                                        {t('amount')}
                                    </TableHead>
                                    <TableHead>{t('paymentMethod')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('date')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {payments.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    payments.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.tenant_name}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                {formatIdr(row.amount)}
                                            </TableCell>
                                            <TableCell>
                                                {row.method ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {row.status}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-xs text-muted-foreground">
                                                {row.paid_at ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() =>
                                                            openEdit(row)
                                                        }
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        onClick={() =>
                                                            remove(row.id)
                                                        }
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
