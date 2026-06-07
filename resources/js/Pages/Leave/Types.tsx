import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
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
import { Pencil, Plus, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type TypeRow = {
    id: number;
    code: string;
    name: string;
    annual_entitlement_days: number;
    is_paid: boolean;
};

export default function Types({
    types,
    flash,
}: PageProps<{ types: TypeRow[] }>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);
    const [editingId, setEditingId] = useState<number | null>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        code: '',
        name: '',
        annual_entitlement_days: '0',
        is_paid: true,
    });

    const openCreate = () => {
        setEditingId(null);
        form.reset();
        form.clearErrors();
        setOpen(true);
    };

    const openEdit = (row: TypeRow) => {
        setEditingId(row.id);
        form.clearErrors();
        form.setData({
            code: row.code,
            name: row.name,
            annual_entitlement_days: String(row.annual_entitlement_days),
            is_paid: row.is_paid,
        });
        setOpen(true);
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setOpen(false);
            setEditingId(null);
            form.reset();
        };
        if (editingId === null) {
            form.post(route('leave.types.store'), { onSuccess });
        } else {
            form.put(route('leave.types.update', editingId), { onSuccess });
        }
    };

    const remove = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('leave.types.destroy', id), { preserveScroll: true });
    };

    return (
        <HrisLayout>
            <Head title={t('leaveTypes')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">{t('leaveTypes')}</h1>
                    <Button onClick={openCreate}>
                        <Plus className="mr-2 h-4 w-4" />
                        {t('addLeaveType')}
                    </Button>
                </div>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('code')}</TableHead>
                                    <TableHead>{t('name')}</TableHead>
                                    <TableHead>{t('annualEntitlement')}</TableHead>
                                    <TableHead>{t('paid')}</TableHead>
                                    <TableHead className="text-right">{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {types.length === 0 ? (
                                    <TableRow>
                                        <TableCell colSpan={5} className="py-8 text-center text-muted-foreground">
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    types.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="font-mono text-sm">{row.code}</TableCell>
                                            <TableCell>{row.name}</TableCell>
                                            <TableCell>{row.annual_entitlement_days}</TableCell>
                                            <TableCell>
                                                <Badge variant={row.is_paid ? 'default' : 'secondary'}>
                                                    {row.is_paid ? t('yes') : t('no')}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-1">
                                                    <Button size="icon" variant="ghost" aria-label={t('edit')} onClick={() => openEdit(row)}>
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                    <Button size="icon" variant="ghost" aria-label={t('delete')} onClick={() => remove(row.id)}>
                                                        <Trash2 className="h-4 w-4" />
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

            <Dialog
                open={open}
                onOpenChange={(o) => {
                    setOpen(o);
                    if (!o) {
                        setEditingId(null);
                        form.reset();
                        form.clearErrors();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editingId === null ? t('addLeaveType') : t('editLeaveType')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submit} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('code')}</Label>
                            <Input value={form.data.code} onChange={(e) => form.setData('code', e.target.value)} required />
                            {form.errors.code && <p className="text-sm text-destructive">{form.errors.code}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('name')}</Label>
                            <Input value={form.data.name} onChange={(e) => form.setData('name', e.target.value)} required />
                            {form.errors.name && <p className="text-sm text-destructive">{form.errors.name}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('annualEntitlement')}</Label>
                            <Input
                                type="number"
                                min="0"
                                max="365"
                                value={form.data.annual_entitlement_days}
                                onChange={(e) => form.setData('annual_entitlement_days', e.target.value)}
                                required
                            />
                            {form.errors.annual_entitlement_days && (
                                <p className="text-sm text-destructive">{form.errors.annual_entitlement_days}</p>
                            )}
                        </div>
                        <div className="flex items-center gap-2">
                            <Checkbox checked={form.data.is_paid} onCheckedChange={(v) => form.setData('is_paid', !!v)} />
                            <Label>{t('paid')}</Label>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={form.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </HrisLayout>
    );
}
