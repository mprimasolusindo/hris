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

export default function Create({
    companies,
    statusOptions,
}: PageProps<{
    companies: CompanyOption[];
    statusOptions: string[];
}>) {
    const { t } = useLanguage();
    const { data, setData, post, processing, errors } = useForm({
        company_id: companies[0]?.id?.toString() ?? '',
        employee_code: '',
        full_name: '',
        email: '',
        phone: '',
        gender: '',
        birth_date: '',
        marital_status: '',
        religion: '',
        status: 'active',
        join_date: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('employees.store'));
    };

    return (
        <HrisLayout>
            <Head title={t('addEmployee')} />

            <div className="mx-auto max-w-2xl space-y-6">
                <h1 className="text-2xl font-bold text-foreground">
                    {t('addEmployee')}
                </h1>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">
                            {t('personalInfo')}
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
                                    {errors.employee_code && (
                                        <p className="text-sm text-destructive">
                                            {errors.employee_code}
                                        </p>
                                    )}
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
                                    {errors.full_name && (
                                        <p className="text-sm text-destructive">
                                            {errors.full_name}
                                        </p>
                                    )}
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
                                    <Label>{t('gender')}</Label>
                                    <Select
                                        value={data.gender || '_none'}
                                        onValueChange={(v) =>
                                            setData('gender', v === '_none' ? '' : v)
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="_none">—</SelectItem>
                                            <SelectItem value="male">{t('male')}</SelectItem>
                                            <SelectItem value="female">{t('female')}</SelectItem>
                                        </SelectContent>
                                    </Select>
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('birthDate')}</Label>
                                    <Input
                                        type="date"
                                        value={data.birth_date}
                                        onChange={(e) =>
                                            setData('birth_date', e.target.value)
                                        }
                                    />
                                </div>
                            </div>

                            <div className="grid gap-4 sm:grid-cols-2">
                                <div className="space-y-2">
                                    <Label>{t('religion')}</Label>
                                    <Input
                                        value={data.religion}
                                        onChange={(e) =>
                                            setData('religion', e.target.value)
                                        }
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('maritalStatus')}</Label>
                                    <Select
                                        value={data.marital_status || '_none'}
                                        onValueChange={(v) =>
                                            setData(
                                                'marital_status',
                                                v === '_none' ? '' : v,
                                            )
                                        }
                                    >
                                        <SelectTrigger>
                                            <SelectValue />
                                        </SelectTrigger>
                                        <SelectContent>
                                            <SelectItem value="_none">—</SelectItem>
                                            <SelectItem value="single">single</SelectItem>
                                            <SelectItem value="married">married</SelectItem>
                                            <SelectItem value="divorced">divorced</SelectItem>
                                            <SelectItem value="widowed">widowed</SelectItem>
                                        </SelectContent>
                                    </Select>
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
