import { useLanguage } from '@/i18n/LanguageContext';
import { CompanyOption, EmployeeDetail } from '@/Features/employees/types';
import { Button } from '@/Components/ui/button';
import {
    Dialog,
    DialogContent,
    DialogFooter,
    DialogHeader,
    DialogTitle,
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
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useEffect } from 'react';

type Props = {
    employee: EmployeeDetail;
    companies: CompanyOption[];
    statusOptions: string[];
    open: boolean;
    onOpenChange: (open: boolean) => void;
};

export function EditEmployeeDialog({
    employee,
    companies,
    statusOptions,
    open,
    onOpenChange,
}: Props) {
    const { t } = useLanguage();
    const { data, setData, patch, processing, errors, reset } = useForm({
        company_id: String(employee.company_id),
        employee_code: employee.employee_code,
        full_name: employee.full_name,
        email: employee.email ?? '',
        phone: employee.phone ?? '',
        gender: employee.gender ?? '',
        birth_date: employee.birth_date ?? '',
        marital_status: employee.marital_status ?? '',
        religion: employee.religion ?? '',
        status: employee.status,
        join_date: employee.join_date ?? '',
        resign_date: employee.resign_date ?? '',
    });

    useEffect(() => {
        if (open) {
            reset();
        }
    }, [open, employee]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        patch(route('employees.update', employee.id), {
            preserveScroll: true,
            onSuccess: () => onOpenChange(false),
        });
    };

    return (
        <Dialog open={open} onOpenChange={onOpenChange}>
            <DialogContent className="max-h-[90vh] max-w-lg overflow-y-auto">
                <DialogHeader>
                    <DialogTitle>{t('edit')} — {employee.full_name}</DialogTitle>
                </DialogHeader>
                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label>{t('company')}</Label>
                        <Select
                            value={data.company_id}
                            onValueChange={(v) => setData('company_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {companies.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {errors.company_id && (
                            <p className="text-sm text-destructive">{errors.company_id}</p>
                        )}
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label>{t('employeeCode')}</Label>
                            <Input
                                value={data.employee_code}
                                onChange={(e) => setData('employee_code', e.target.value)}
                                required
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>{t('name')}</Label>
                            <Input
                                value={data.full_name}
                                onChange={(e) => setData('full_name', e.target.value)}
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
                                onChange={(e) => setData('email', e.target.value)}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>{t('phone')}</Label>
                            <Input
                                value={data.phone}
                                onChange={(e) => setData('phone', e.target.value)}
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
                                onChange={(e) => setData('birth_date', e.target.value)}
                            />
                        </div>
                    </div>
                    <div className="grid gap-4 sm:grid-cols-2">
                        <div className="space-y-2">
                            <Label>{t('status')}</Label>
                            <Select
                                value={data.status}
                                onValueChange={(v) => setData('status', v)}
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
                                onChange={(e) => setData('join_date', e.target.value)}
                            />
                        </div>
                    </div>
                    <DialogFooter>
                        <Button type="button" variant="outline" onClick={() => onOpenChange(false)}>
                            {t('cancel')}
                        </Button>
                        <Button type="submit" disabled={processing}>
                            {t('save')}
                        </Button>
                    </DialogFooter>
                </form>
            </DialogContent>
        </Dialog>
    );
}
