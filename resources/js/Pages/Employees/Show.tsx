import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { EmployeeStatusBadge } from '@/Features/employees/components/EmployeeStatusBadge';
import { EmployeeProfileTabs } from '@/Features/employees/components/EmployeeProfileTabs';
import {
    CompanyOption,
    EmployeeDetail,
    ManagerOption,
    NamedOption,
    SalaryComponentOption,
    UserOption,
} from '@/Features/employees/types';
import { ArrowLeft, Trash2, Upload } from 'lucide-react';
import { PageProps } from '@/types';
import { FormEventHandler, useRef } from 'react';
import { toast } from 'sonner';
import { useEffect } from 'react';

export default function Show({
    employee,
    attendanceSummary,
    payrollEstimate,
    salaryComponents,
    users,
    statusOptions,
    companies,
    departments,
    positions,
    sites,
    managers,
    flash,
}: PageProps<{
    employee: EmployeeDetail;
    attendanceSummary: Record<string, number>;
    payrollEstimate: {
        gross_allowances: number;
        total_deductions: number;
        estimated_net: number;
    };
    salaryComponents: SalaryComponentOption[];
    users: UserOption[];
    statusOptions: string[];
    companies: CompanyOption[];
    departments: NamedOption[];
    positions: NamedOption[];
    sites: NamedOption[];
    managers: ManagerOption[];
}>) {
    const { t } = useLanguage();
    const photoRef = useRef<HTMLInputElement>(null);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const archiveEmployee = () => {
        if (!window.confirm('Archive this employee?')) return;
        router.patch(route('employees.archive', employee.id));
    };

    const onPhotoChange: FormEventHandler<HTMLFormElement> = (e) => {
        e.preventDefault();
        const file = photoRef.current?.files?.[0];
        if (!file) return;
        router.post(
            route('employees.photo.store', employee.id),
            { photo: file },
            { forceFormData: true, preserveScroll: true },
        );
    };

    return (
        <HrisLayout>
            <Head title={employee.full_name} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-start justify-between gap-4">
                    <div className="flex items-start gap-4">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('employees.index')}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            {employee.profile_photo_url ? (
                                <img
                                    src={employee.profile_photo_url}
                                    alt=""
                                    className="mb-2 h-16 w-16 rounded-full object-cover"
                                />
                            ) : null}
                            <h1 className="text-2xl font-bold text-foreground">
                                {employee.full_name}
                            </h1>
                            <p className="text-sm text-muted-foreground">
                                {employee.employee_code}
                            </p>
                            <div className="mt-2">
                                <EmployeeStatusBadge status={employee.status} />
                            </div>
                        </div>
                    </div>
                    <div className="flex flex-wrap gap-2">
                        <form onSubmit={onPhotoChange}>
                            <input
                                ref={photoRef}
                                type="file"
                                accept="image/*"
                                className="hidden"
                                onChange={() =>
                                    photoRef.current?.form?.requestSubmit()
                                }
                            />
                            <Button
                                type="button"
                                variant="outline"
                                size="sm"
                                onClick={() => photoRef.current?.click()}
                            >
                                <Upload className="mr-2 h-4 w-4" />
                                Photo
                            </Button>
                        </form>
                        <Button
                            variant="destructive"
                            size="sm"
                            onClick={archiveEmployee}
                        >
                            <Trash2 className="mr-2 h-4 w-4" />
                            Archive
                        </Button>
                    </div>
                </div>

                <div className="grid gap-4 md:grid-cols-2 lg:grid-cols-4">
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                {t('company')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="font-medium">
                            {employee.company_name ?? '—'}
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                {t('department')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="font-medium">
                            {employee.department_name ?? '—'}
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                {t('site')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="font-medium">
                            {employee.site_name ?? '—'}
                        </CardContent>
                    </Card>
                    <Card className="shadow-sm">
                        <CardHeader className="pb-2">
                            <CardTitle className="text-sm text-muted-foreground">
                                {t('joinDate')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent className="font-medium">
                            {employee.join_date ?? '—'}
                        </CardContent>
                    </Card>
                </div>

                <EmployeeProfileTabs
                    employee={employee}
                    companies={companies}
                    statusOptions={statusOptions}
                    salaryComponents={salaryComponents}
                    users={users}
                    departments={departments}
                    positions={positions}
                    sites={sites}
                    managers={managers}
                    attendanceSummary={attendanceSummary}
                    payrollEstimate={payrollEstimate}
                />
            </div>
        </HrisLayout>
    );
}
