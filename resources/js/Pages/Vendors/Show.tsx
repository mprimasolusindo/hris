import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
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
import { FormEventHandler, useEffect } from 'react';
import { ArrowLeft, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type PlacementRow = {
    id: number;
    employee_name: string | null;
    employee_code: string | null;
    employer_name: string | null;
    status: string | null;
};

export default function Show({
    vendor,
    flash,
}: PageProps<{
    vendor: {
        id: number;
        name: string;
        sites: Array<{ id: number; name: string; location: string | null }>;
        placements: PlacementRow[];
    };
}>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({ name: vendor.name });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.put(route('vendors.update', vendor.id));
    };

    const destroy = () => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('vendors.destroy', vendor.id));
    };

    return (
        <HrisLayout>
            <Head title={vendor.name} />

            <div className="space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('vendors.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <h1 className="text-2xl font-bold text-foreground">{vendor.name}</h1>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('edit')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="flex flex-wrap items-end gap-3">
                            <div className="space-y-2">
                                <Label>{t('vendorName')}</Label>
                                <Input
                                    value={form.data.name}
                                    onChange={(e) => form.setData('name', e.target.value)}
                                    required
                                />
                            </div>
                            <Button type="submit" disabled={form.processing}>
                                {t('save')}
                            </Button>
                            <Button type="button" variant="destructive" onClick={destroy}>
                                <Trash2 className="mr-2 h-4 w-4" />
                                {t('delete')}
                            </Button>
                        </form>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('sites')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('name')}</TableHead>
                                    <TableHead>{t('address')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {vendor.sites.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={2}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    vendor.sites.map((site) => (
                                        <TableRow key={site.id}>
                                            <TableCell>{site.name}</TableCell>
                                            <TableCell>{site.location ?? '-'}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between">
                        <CardTitle>{t('placements')}</CardTitle>
                        <Button variant="outline" size="sm" asChild>
                            <Link href={route('outsourcing.index', { vendor_id: vendor.id })}>
                                {t('assignPlacement')}
                            </Link>
                        </Button>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('employeeCode')}</TableHead>
                                    <TableHead>{t('companies')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {vendor.placements.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="py-6 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    vendor.placements.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>{row.employee_name}</TableCell>
                                            <TableCell>{row.employee_code}</TableCell>
                                            <TableCell>{row.employer_name}</TableCell>
                                            <TableCell>{row.status}</TableCell>
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
