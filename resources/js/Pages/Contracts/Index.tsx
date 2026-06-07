import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
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
import { AlertTriangle, Plus } from 'lucide-react';
import { toast } from 'sonner';

type ContractRow = {
    id: number;
    employee_name: string | null;
    employee_code: string | null;
    contract_type: string;
    start_date: string;
    end_date: string | null;
    salary_base: string;
    derived_status: string;
    is_expiring: boolean;
};

export default function Index({
    contracts,
    filters,
    contractTypes,
    employees,
    expiringCount,
    flash,
}: PageProps<{
    contracts: ContractRow[];
    filters: { type: string; status: string };
    contractTypes: string[];
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
    expiringCount: number;
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        employee_id: String(employees[0]?.id ?? ''),
        contract_type: contractTypes[0] ?? 'pkwt',
        start_date: '',
        end_date: '',
        salary_base: '',
    });

    const applyFilters = (patch: Partial<typeof filters>) => {
        router.get(route('contracts.index'), { ...filters, ...patch });
    };

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('contracts.store'));
    };

    const formatMoney = (v: string) =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(Number(v));

    return (
        <HrisLayout>
            <Head title={t('contracts')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">{t('contracts')}</h1>
                        {expiringCount > 0 && (
                            <p className="mt-1 flex items-center gap-1 text-sm text-amber-600">
                                <AlertTriangle className="h-4 w-4" />
                                {expiringCount} {t('expiringSoon')}
                            </p>
                        )}
                    </div>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addContract')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('addContract')}</DialogTitle>
                            </DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
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
                                                    {e.full_name}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('contractType')}</Label>
                                    <Select
                                        value={form.data.contract_type}
                                        onValueChange={(v) => form.setData('contract_type', v)}
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {contractTypes.map((ct) => (
                                                <SelectItem key={ct} value={ct}>
                                                    {ct.toUpperCase()}
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
                                                form.setData('start_date', e.target.value)
                                            }
                                            required
                                        />
                                    </div>
                                    <div className="space-y-2">
                                        <Label>{t('endDate')}</Label>
                                        <Input
                                            type="date"
                                            value={form.data.end_date}
                                            onChange={(e) =>
                                                form.setData('end_date', e.target.value)
                                            }
                                        />
                                    </div>
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('grossSalary')}</Label>
                                    <Input
                                        type="number"
                                        value={form.data.salary_base}
                                        onChange={(e) =>
                                            form.setData('salary_base', e.target.value)
                                        }
                                        required
                                    />
                                </div>
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap gap-3 p-4">
                        <Select
                            value={filters.type || 'all'}
                            onValueChange={(v) =>
                                applyFilters({ type: v === 'all' ? '' : v })
                            }
                        >
                            <SelectTrigger className="w-44">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {contractTypes.map((ct) => (
                                    <SelectItem key={ct} value={ct}>
                                        {ct.toUpperCase()}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        <Select
                            value={filters.status || 'all'}
                            onValueChange={(v) =>
                                applyFilters({ status: v === 'all' ? '' : v })
                            }
                        >
                            <SelectTrigger className="w-44">
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                <SelectItem value="active">{t('active')}</SelectItem>
                                <SelectItem value="expired">{t('expired')}</SelectItem>
                                <SelectItem value="expiring">{t('expiringSoon')}</SelectItem>
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
                                    <TableHead>{t('contractType')}</TableHead>
                                    <TableHead>{t('startDate')}</TableHead>
                                    <TableHead>{t('endDate')}</TableHead>
                                    <TableHead>{t('grossSalary')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {contracts.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    contracts.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                <Link
                                                    href={route('contracts.show', row.id)}
                                                    className="font-medium text-primary hover:underline"
                                                >
                                                    {row.employee_name}
                                                </Link>
                                            </TableCell>
                                            <TableCell>{row.contract_type.toUpperCase()}</TableCell>
                                            <TableCell>{row.start_date}</TableCell>
                                            <TableCell>{row.end_date ?? '-'}</TableCell>
                                            <TableCell>{formatMoney(row.salary_base)}</TableCell>
                                            <TableCell>
                                                <Badge
                                                    variant={
                                                        row.is_expiring ? 'destructive' : 'outline'
                                                    }
                                                >
                                                    {row.derived_status}
                                                </Badge>
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
