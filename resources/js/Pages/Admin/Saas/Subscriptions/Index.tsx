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

type SubscriptionRow = {
    id: number;
    tenant_id: number;
    tenant_name: string | null;
    plan_id: number;
    plan_name: string | null;
    start_date: string | null;
    end_date: string | null;
    status: string;
};

const STATUSES = ['active', 'trialing', 'past_due', 'cancelled', 'expired'];

export default function Index({
    subscriptions,
    tenants,
    plans,
    flash,
}: PageProps<{
    subscriptions: SubscriptionRow[];
    tenants: Array<{ id: number; name: string }>;
    plans: Array<{ id: number; name: string }>;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);
    const [editId, setEditId] = useState<number | null>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        tenant_id: String(tenants[0]?.id ?? ''),
        plan_id: String(plans[0]?.id ?? ''),
        start_date: new Date().toISOString().slice(0, 10),
        end_date: '',
        status: 'active',
    });

    const openCreate = () => {
        setEditId(null);
        form.setData({
            tenant_id: String(tenants[0]?.id ?? ''),
            plan_id: String(plans[0]?.id ?? ''),
            start_date: new Date().toISOString().slice(0, 10),
            end_date: '',
            status: 'active',
        });
        setOpen(true);
    };

    const openEdit = (row: SubscriptionRow) => {
        setEditId(row.id);
        form.setData({
            tenant_id: String(row.tenant_id),
            plan_id: String(row.plan_id),
            start_date: row.start_date ?? '',
            end_date: row.end_date ?? '',
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
            form.put(route('admin.saas.subscriptions.update', editId), {
                onSuccess,
            });
        } else {
            form.post(route('admin.saas.subscriptions.store'), { onSuccess });
        }
    };

    const remove = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('admin.saas.subscriptions.destroy', id));
    };

    return (
        <HrisLayout>
            <Head title={t('subscriptions')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">
                        {t('subscriptions')}
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
                                {t('addSubscription')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    {editId
                                        ? t('edit')
                                        : t('addSubscription')}
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
                                    <Label>{t('planName')}</Label>
                                    <Select
                                        value={form.data.plan_id}
                                        onValueChange={(v) =>
                                            form.setData('plan_id', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {plans.map((pl) => (
                                                <SelectItem
                                                    key={pl.id}
                                                    value={String(pl.id)}
                                                >
                                                    {pl.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="grid grid-cols-2 gap-4">
                                    <div className="space-y-2">
                                        <Label>{t('startDate')}</Label>
                                        <Input
                                            type="date"
                                            value={form.data.start_date}
                                            onChange={(e) =>
                                                form.setData(
                                                    'start_date',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>{t('endDate')}</Label>
                                        <Input
                                            type="date"
                                            value={form.data.end_date}
                                            onChange={(e) =>
                                                form.setData(
                                                    'end_date',
                                                    e.target.value,
                                                )
                                            }
                                        />
                                    </div>
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
                                    <TableHead>{t('planName')}</TableHead>
                                    <TableHead>{t('startDate')}</TableHead>
                                    <TableHead>{t('endDate')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {subscriptions.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    subscriptions.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.tenant_name}
                                            </TableCell>
                                            <TableCell>
                                                {row.plan_name}
                                            </TableCell>
                                            <TableCell>
                                                {row.start_date}
                                            </TableCell>
                                            <TableCell>
                                                {row.end_date ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                <Badge variant="outline">
                                                    {row.status}
                                                </Badge>
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
