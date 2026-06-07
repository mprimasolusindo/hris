import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import { Checkbox } from '@/Components/ui/checkbox';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Link } from '@inertiajs/react';
import { Eye, Plus, Pencil, Trash2 } from 'lucide-react';
import { FormEventHandler, ReactNode, useEffect, useState } from 'react';
import { toast } from 'sonner';

export type CrudField = {
    name: string;
    label: string;
    type:
        | 'text'
        | 'time'
        | 'date'
        | 'datetime-local'
        | 'number'
        | 'textarea'
        | 'select'
        | 'checkbox';
    required?: boolean;
    options?: Array<{ value: string; label: string }>;
    min?: number;
    max?: number;
    step?: number;
};

export type CrudColumn<T> = {
    key: keyof T | string;
    label: string;
    render?: (row: T) => ReactNode;
};

type MasterCrudPageProps<T extends { id: number }> = {
    title: string;
    pageTitle: string;
    addLabel: string;
    items: T[];
    columns: CrudColumn<T>[];
    fields: CrudField[];
    initialForm: Record<string, string | boolean>;
    storeUrl: string;
    updateUrl: (id: number) => string;
    destroyUrl: (id: number) => string;
    flash?: { success?: string | null };
    mapRowToForm?: (row: T) => Record<string, string | boolean>;
    toolbar?: ReactNode;
    detailUrl?: (id: number) => string;
};

export default function MasterCrudPage<T extends { id: number }>({
    title,
    pageTitle,
    addLabel,
    items,
    columns,
    fields,
    initialForm,
    storeUrl,
    updateUrl,
    destroyUrl,
    flash,
    mapRowToForm,
    toolbar,
    detailUrl,
}: MasterCrudPageProps<T>) {
    const { t } = useLanguage();
    const [dialogOpen, setDialogOpen] = useState(false);
    const [editId, setEditId] = useState<number | null>(null);

    const { data, setData, post, put, processing, reset, errors } = useForm(
        initialForm as Record<string, string | boolean>,
    );

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash?.success]);

    const openCreate = () => {
        setEditId(null);
        reset();
        setDialogOpen(true);
    };

    const openEdit = (row: T) => {
        setEditId(row.id);
        const values = mapRowToForm
            ? mapRowToForm(row)
            : (row as unknown as Record<string, string | boolean>);
        Object.entries(values).forEach(([key, value]) => {
            setData(key as keyof typeof data, value as never);
        });
        setDialogOpen(true);
    };

    const closeDialog = () => {
        setDialogOpen(false);
        setEditId(null);
        reset();
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        if (editId) {
            put(updateUrl(editId), {
                onSuccess: () => closeDialog(),
            });
        } else {
            post(storeUrl, {
                onSuccess: () => closeDialog(),
            });
        }
    };

    const destroy = (id: number) => {
        if (!window.confirm(t('delete') + '?')) {
            return;
        }
        router.delete(destroyUrl(id));
    };

    const renderField = (field: CrudField) => {
        const value = data[field.name];

        if (field.type === 'checkbox') {
            return (
                <div key={field.name} className="flex items-center gap-2">
                    <Checkbox
                        id={field.name}
                        checked={Boolean(value)}
                        onCheckedChange={(checked) =>
                            setData(field.name, Boolean(checked))
                        }
                    />
                    <Label htmlFor={field.name}>{field.label}</Label>
                </div>
            );
        }

        if (field.type === 'textarea') {
            return (
                <div key={field.name} className="space-y-2">
                    <Label>{field.label}</Label>
                    <Textarea
                        value={String(value ?? '')}
                        onChange={(e) => setData(field.name, e.target.value)}
                        required={field.required}
                    />
                    {errors[field.name] && (
                        <p className="text-sm text-destructive">{errors[field.name]}</p>
                    )}
                </div>
            );
        }

        if (field.type === 'select') {
            return (
                <div key={field.name} className="space-y-2">
                    <Label>{field.label}</Label>
                    <Select
                        value={String(value ?? '')}
                        onValueChange={(v) => setData(field.name, v)}
                    >
                        <SelectTrigger>
                            <SelectValue />
                        </SelectTrigger>
                        <SelectContent>
                            {field.options?.map((opt) => (
                                <SelectItem key={opt.value} value={opt.value}>
                                    {opt.label}
                                </SelectItem>
                            ))}
                        </SelectContent>
                    </Select>
                    {errors[field.name] && (
                        <p className="text-sm text-destructive">
                            {errors[field.name]}
                        </p>
                    )}
                </div>
            );
        }

        return (
            <div key={field.name} className="space-y-2">
                <Label>{field.label}</Label>
                <Input
                    type={field.type}
                    value={String(value ?? '')}
                    onChange={(e) => setData(field.name, e.target.value)}
                    required={field.required}
                    min={field.min}
                    max={field.max}
                    step={field.step}
                />
                {errors[field.name] && (
                    <p className="text-sm text-destructive">{errors[field.name]}</p>
                )}
            </div>
        );
    };

    return (
        <HrisLayout>
            <Head title={pageTitle} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold text-foreground">{title}</h1>
                    <div className="flex flex-wrap items-center gap-2">
                        {toolbar}
                        <Dialog
                        open={dialogOpen}
                        onOpenChange={(open) => {
                            if (!open) {
                                closeDialog();
                            } else {
                                setDialogOpen(true);
                            }
                        }}
                    >
                        <DialogTrigger asChild>
                            <Button onClick={openCreate}>
                                <Plus className="mr-2 h-4 w-4" />
                                {addLabel}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>
                                    {editId ? t('edit') : addLabel}
                                </DialogTitle>
                            </DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                {fields.map(renderField)}
                                <div className="flex justify-end gap-2">
                                    <Button
                                        type="button"
                                        variant="outline"
                                        onClick={closeDialog}
                                    >
                                        {t('cancel')}
                                    </Button>
                                    <Button type="submit" disabled={processing}>
                                        {t('save')}
                                    </Button>
                                </div>
                            </form>
                        </DialogContent>
                    </Dialog>
                    </div>
                </div>

                <Card className="shadow-sm">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    {columns.map((col) => (
                                        <TableHead key={String(col.key)}>
                                            {col.label}
                                        </TableHead>
                                    ))}
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {items.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={columns.length + 1}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    items.map((row) => (
                                        <TableRow key={row.id}>
                                            {columns.map((col) => (
                                                <TableCell key={String(col.key)}>
                                                    {col.render
                                                        ? col.render(row)
                                                        : String(
                                                              (row as Record<string, unknown>)[
                                                                  col.key as string
                                                              ] ?? '-',
                                                          )}
                                                </TableCell>
                                            ))}
                                            <TableCell>
                                                <div className="flex gap-1">
                                                    {detailUrl && (
                                                        <Button
                                                            variant="ghost"
                                                            size="icon"
                                                            asChild
                                                        >
                                                            <Link href={detailUrl(row.id)}>
                                                                <Eye className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() => openEdit(row)}
                                                    >
                                                        <Pencil className="h-4 w-4" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() => destroy(row.id)}
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
