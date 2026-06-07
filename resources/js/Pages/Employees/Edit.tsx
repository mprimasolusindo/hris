import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { PageProps } from '@/types';
import { FormEventHandler } from 'react';

type CompanyOption = { id: number; name: string };

type EmployeeForm = {
    id: number;
    company_id: number;
    employee_code: string;
    full_name: string;
    email: string | null;
    phone: string | null;
    status: string;
    join_date: string | null;
    resign_date: string | null;
};

export default function Edit({
    employee,
    companies,
    statusOptions,
}: PageProps<{
    employee: EmployeeForm;
    companies: CompanyOption[];
    statusOptions: string[];
}>) {
    const { t } = useLanguage();
    const { data, setData, put, processing, errors } = useForm({
        company_id: String(employee.company_id),
        employee_code: employee.employee_code,
        full_name: employee.full_name,
        email: employee.email ?? '',
        phone: employee.phone ?? '',
        status: employee.status,
        join_date: employee.join_date ?? '',
        resign_date: employee.resign_date ?? '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        put(route('employees.update', employee.id));
    };

    return (
        <HrisLayout>
            <Head title={t('editEmployee')} />

            <div className="mx-auto max-w-2xl space-y-6">
                <h1 className="text-2xl font-bold text-foreground">
                    {t('editEmployee')}
                </h1>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">
                            {employee.full_name}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-4">
                            <div className="space-y-2">
                                <Label>{t('company')}</Label>
                                <Select
                                    value={data.company_id}
                                    onValueChange={(v) =>
                                        setData('company_id', v)
                                    }
                                >
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {companies.map((c) => (
                                            <SelectItem
                                                key={c.id}
                                                value={String(c.id)}
                                            >
                                                {c.name}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                                {errors.company_id && (
                                    <p className="text-sm text-destructive">
                                        {errors.company_id}
                                    </p>
                                )}
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>{t('employeeCode')}</Label>
                                    <Input
                                        value={data.employee_code}
                                        onChange={(e) =>
                                            setData(
                                                'employee_code',
                                                e.target.value,
                                            )
                                        }
                                        required
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('name')}</Label>
                                    <Input
                                        value={data.full_name}
                                        onChange={(e) =>
                                            setData('full_name', e.target.value)
                                        }
                                        required
                                    />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>{t('email')}</Label>
                                    <Input
                                        type="email"
                                        value={data.email}
                                        onChange={(e) =>
                                            setData('email', e.target.value)
                                        }
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('phone')}</Label>
                                    <Input
                                        value={data.phone}
                                        onChange={(e) =>
                                            setData('phone', e.target.value)
                                        }
                                    />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>{t('status')}</Label>
                                    <Select
                                        value={data.status}
                                        onValueChange={(v) =>
                                            setData('status', v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            {statusOptions.map((s) => (
                                                <SelectItem key={s} value={s}>
                                                    {s}
                                                </SelectItem>
                                            ))}
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('joinDate')}</Label>
                                    <Input
                                        type="date"
                                        value={data.join_date}
                                        onChange={(e) =>
                                            setData('join_date', e.target.value)
                                        }
                                    />
                                </div>
                            </div>

                            <div className="space-y-2">
                                <Label>Resign date</Label>
                                <Input
                                    type="date"
                                    value={data.resign_date}
                                    onChange={(e) =>
                                        setData('resign_date', e.target.value)
                                    }
                                />
                            </div>

                            <div className="flex gap-2 pt-2">
                                <Button type="submit" disabled={processing}>
                                    {t('save')}
                                </Button>
                                <Button variant="outline" asChild>
                                    <Link href={route('employees.index')}>
                                        {t('cancel')}
                                    </Link>
                                </Button>
                            </div>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
