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

type PlacementRow = {
    id: number;
    vendor_id: number;
    employee_id: number;
    vendor_name: string | null;
    employee_name: string | null;
    employee_code: string | null;
    employer_name: string | null;
    site_name: string | null;
    status: string;
};

export default function Index({
    placements,
    filters,
    vendors,
    employees,
    flash,
}: PageProps<{
    placements: PlacementRow[];
    filters: { vendor_id: string };
    vendors: Array<{ id: number; name: string }>;
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);
    const [editId, setEditId] = useState<number | null>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        vendor_id: String(vendors[0]?.id ?? ''),
        employee_id: String(employees[0]?.id ?? ''),
    });

    const applyVendorFilter = (vendorId: string) => {
        router.get(route('outsourcing.index'), {
            vendor_id: vendorId === 'all' ? '' : vendorId,
        });
    };

    const openCreate = () => {
        setEditId(null);
        form.setData({
            vendor_id: String(vendors[0]?.id ?? ''),
            employee_id: String(employees[0]?.id ?? ''),
        });
        setOpen(true);
    };

    const openEdit = (row: PlacementRow) => {
        setEditId(row.id);
        form.setData({
            vendor_id: String(row.vendor_id),
            employee_id: String(row.employee_id),
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
            form.put(route('outsourcing.update', editId), { onSuccess });
        } else {
            form.post(route('outsourcing.store'), { onSuccess });
        }
    };

    const remove = (id: number) => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('outsourcing.destroy', id));
    };

    return (
        <HrisLayout>
            <Head title={t('placements')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">{t('placements')}</h1>
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
                                {t('assignPlacement')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    {editId
                                        ? t('editPlacement')
                                        : t('assignPlacement')}
                                </DialogTitle>
                            </DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label>{t('vendors')}</Label>
                                    <Select
                                        value={form.data.vendor_id}
                                        onValueChange={(v) => form.setData('vendor_id', v)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {vendors.map((v) => (
                                                <SelectItem key={v.id} value={String(v.id)}>
                                                    {v.name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('employee')}</Label>
                                    <Select
                                        value={form.data.employee_id}
                                        onValueChange={(v) => form.setData('employee_id', v)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {employees.map((e) => (
                                                <SelectItem key={e.id} value={String(e.id)}>
                                                    {e.full_name} ({e.employee_code})
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card>
                    <CardContent className="p-4">
                        <Select
                            value={filters.vendor_id || 'all'}
                            onValueChange={applyVendorFilter}
                        >
                            <SelectTrigger className="w-56">
                                <SelectValue placeholder={t('vendors')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {vendors.map((v) => (
                                    <SelectItem key={v.id} value={String(v.id)}>
                                        {v.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('vendors')}</TableHead>
                                    <TableHead>{t('companies')}</TableHead>
                                    <TableHead>{t('site')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {placements.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    placements.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.employee_name}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {row.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell>{row.vendor_name}</TableCell>
                                            <TableCell>{row.employer_name}</TableCell>
                                            <TableCell>{row.site_name ?? '-'}</TableCell>
                                            <TableCell>
                                                <Badge variant="outline">{row.status}</Badge>
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() =>
                                                            openEdit(row)
                                                        }
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
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
