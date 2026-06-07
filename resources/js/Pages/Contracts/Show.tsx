import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { PageProps } from '@/types';
import { FormEventHandler, useEffect } from 'react';
import { ArrowLeft, Trash2 } from 'lucide-react';
import { toast } from 'sonner';

type ContractData = {
    id: number;
    employee_id: number;
    employee_name: string | null;
    employee_code: string | null;
    contract_type: string;
    start_date: string;
    end_date: string | null;
    salary_base: string;
    derived_status: string;
    is_expiring: boolean;
    days_until_end: number | null;
};

export default function Show({
    contract,
    flash,
}: PageProps<{ contract: ContractData }>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        employee_id: String(contract.employee_id),
        contract_type: contract.contract_type,
        start_date: contract.start_date,
        end_date: contract.end_date ?? '',
        salary_base: contract.salary_base,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.put(route('contracts.update', contract.id));
    };

    const destroy = () => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('contracts.destroy', contract.id));
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

            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('contracts.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div>
                        <h1 className="text-2xl font-bold text-foreground">
                            {contract.employee_name}
                        </h1>
                        <p className="text-sm text-muted-foreground">{contract.employee_code}</p>
                    </div>
                    <Badge className="ml-auto" variant={contract.is_expiring ? 'destructive' : 'outline'}>
                        {contract.derived_status}
                    </Badge>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('edit')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
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
                                        {['pkwt', 'pkwtt', 'outsourcing', 'magang'].map((ct) => (
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
                                        onChange={(e) => form.setData('start_date', e.target.value)}
                                        required
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('endDate')}</Label>
                                    <Input
                                        type="date"
                                        value={form.data.end_date}
                                        onChange={(e) => form.setData('end_date', e.target.value)}
                                    />
                                </div>
                            </div>
                            <div className="space-y-2">
                                <Label>{t('grossSalary')}</Label>
                                <Input
                                    type="number"
                                    value={form.data.salary_base}
                                    onChange={(e) => form.setData('salary_base', e.target.value)}
                                    required
                                />
                            </div>
                            <p className="text-sm text-muted-foreground">
                                {t('grossSalary')}: {formatMoney(contract.salary_base)}
                            </p>
                            <div className="flex justify-between gap-2">
                                <Button type="button" variant="destructive" onClick={destroy}>
                                    <Trash2 className="mr-2 h-4 w-4" />
                                    {t('delete')}
                                </Button>
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
