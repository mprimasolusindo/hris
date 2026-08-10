import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Textarea } from '@/Components/ui/textarea';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { useForm } from '@inertiajs/react';
import { FormEventHandler, useMemo } from 'react';

export type JobFormLookups = {
    companies: Array<{ id: number; name: string }>;
    sites: Array<{ id: number; name: string; company_id: number }>;
    departments: Array<{ id: number; name: string; company_id: number }>;
    positions: Array<{ id: number; name: string }>;
    employees: Array<{ id: number; full_name: string; employee_code: string }>;
    employmentTypes: string[];
    priorities: string[];
    statusOptions: string[];
};

export type JobFormValues = {
    company_id: string;
    site_id: string;
    department_id: string;
    position_id: string;
    title: string;
    code: string;
    employment_type: string;
    headcount: string;
    priority: string;
    opened_at: string;
    target_close_date: string;
    hiring_manager_id: string;
    recruiter_id: string;
    salary_min: string;
    salary_max: string;
    currency: string;
    description: string;
    requirements: string;
    benefits: string;
    location_note: string;
    status: string;
};

export const emptyJobForm = (defaults?: Partial<JobFormValues>): JobFormValues => ({
    company_id: '',
    site_id: '',
    department_id: '',
    position_id: '',
    title: '',
    code: '',
    employment_type: 'permanent',
    headcount: '1',
    priority: 'medium',
    opened_at: '',
    target_close_date: '',
    hiring_manager_id: '',
    recruiter_id: '',
    salary_min: '',
    salary_max: '',
    currency: 'IDR',
    description: '',
    requirements: '',
    benefits: '',
    location_note: '',
    status: 'draft',
    ...defaults,
});

type Props = {
    formOptions: JobFormLookups;
    initial: JobFormValues;
    mode: 'create' | 'edit';
    jobId?: number;
    onCancel?: () => void;
    onSuccess?: () => void;
    showDelete?: boolean;
    onDelete?: () => void;
};

export default function JobPostingForm({
    formOptions,
    initial,
    mode,
    jobId,
    onCancel,
    onSuccess,
    showDelete,
    onDelete,
}: Props) {
    const { t } = useLanguage();
    const form = useForm({ ...initial, publish: false as boolean });

    const filteredSites = useMemo(
        () =>
            formOptions.sites.filter(
                (s) => !form.data.company_id || String(s.company_id) === form.data.company_id,
            ),
        [formOptions.sites, form.data.company_id],
    );

    const filteredDepts = useMemo(
        () =>
            formOptions.departments.filter(
                (d) => !form.data.company_id || String(d.company_id) === form.data.company_id,
            ),
        [formOptions.departments, form.data.company_id],
    );

    const employmentLabel = (value: string) => {
        if (value === 'permanent') return t('permanent');
        if (value === 'contract') return t('contract');
        if (value === 'outsourced') return t('outsourced');
        if (value === 'internship') return t('internship');
        return value;
    };

    const priorityLabel = (value: string) => {
        if (value === 'low') return t('low');
        if (value === 'medium') return t('medium');
        if (value === 'high') return t('high');
        return value;
    };

    const submit = (publish: boolean) => {
        form.transform((data) => ({ ...data, publish }));
        const opts = {
            onSuccess: () => {
                form.transform((data) => data);
                onSuccess?.();
            },
            onFinish: () => {
                form.transform((data) => data);
            },
        };
        if (mode === 'create') {
            form.post(route('recruitment.jobs.store'), opts);
        } else if (jobId) {
            form.put(route('recruitment.jobs.update', jobId), opts);
        }
    };

    const onSubmit: FormEventHandler = (e) => {
        e.preventDefault();
        submit(false);
    };

    const setOptionalSelect = (key: keyof JobFormValues, value: string) => {
        form.setData(key, value === '__none__' ? '' : value);
    };

    const optionalValue = (value: string) => (value === '' ? '__none__' : value);

    return (
        <form onSubmit={onSubmit} className="space-y-6">
            <section className="space-y-3">
                <div className="text-xs font-medium uppercase text-muted-foreground">
                    {t('jobFormBasics')}
                </div>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Label>
                            {t('jobCode')} *
                        </Label>
                        <Input
                            value={form.data.code}
                            onChange={(e) => form.setData('code', e.target.value)}
                            required
                        />
                        {form.errors.code && (
                            <p className="text-sm text-destructive">{form.errors.code}</p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label>{t('employmentType')}</Label>
                        <Select
                            value={form.data.employment_type}
                            onValueChange={(v) => form.setData('employment_type', v)}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {formOptions.employmentTypes.map((v) => (
                                    <SelectItem key={v} value={v}>
                                        {employmentLabel(v)}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-2 sm:col-span-2">
                        <Label>
                            {t('jobTitle')} *
                        </Label>
                        <Input
                            value={form.data.title}
                            onChange={(e) => form.setData('title', e.target.value)}
                            required
                        />
                        {form.errors.title && (
                            <p className="text-sm text-destructive">{form.errors.title}</p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label>
                            {t('company')} *
                        </Label>
                        <Select
                            value={form.data.company_id}
                            onValueChange={(v) =>
                                form.setData({
                                    ...form.data,
                                    company_id: v,
                                    site_id: '',
                                    department_id: '',
                                })
                            }
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="—" />
                            </SelectTrigger>
                            <SelectContent>
                                {formOptions.companies.map((c) => (
                                    <SelectItem key={c.id} value={String(c.id)}>
                                        {c.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                        {form.errors.company_id && (
                            <p className="text-sm text-destructive">{form.errors.company_id}</p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label>{t('site')}</Label>
                        <Select
                            value={optionalValue(form.data.site_id)}
                            onValueChange={(v) => setOptionalSelect('site_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="—" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">—</SelectItem>
                                {filteredSites.map((s) => (
                                    <SelectItem key={s.id} value={String(s.id)}>
                                        {s.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-2">
                        <Label>{t('department')}</Label>
                        <Select
                            value={optionalValue(form.data.department_id)}
                            onValueChange={(v) => {
                                const next = v === '__none__' ? '' : v;
                                form.setData({
                                    ...form.data,
                                    department_id: next,
                                    position_id: '',
                                });
                            }}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="—" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">—</SelectItem>
                                {filteredDepts.map((d) => (
                                    <SelectItem key={d.id} value={String(d.id)}>
                                        {d.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-2">
                        <Label>{t('position')}</Label>
                        <Select
                            value={optionalValue(form.data.position_id)}
                            onValueChange={(v) => {
                                if (v === '__none__') {
                                    form.setData('position_id', '');
                                    return;
                                }
                                const pos = formOptions.positions.find((p) => String(p.id) === v);
                                form.setData({
                                    ...form.data,
                                    position_id: v,
                                    title: form.data.title || pos?.name || '',
                                });
                            }}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="—" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">—</SelectItem>
                                {formOptions.positions.map((p) => (
                                    <SelectItem key={p.id} value={String(p.id)}>
                                        {p.name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </section>

            <section className="space-y-3">
                <div className="text-xs font-medium uppercase text-muted-foreground">
                    {t('jobFormPlan')}
                </div>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Label>{t('headcount')}</Label>
                        <Input
                            type="number"
                            min={1}
                            value={form.data.headcount}
                            onChange={(e) => form.setData('headcount', e.target.value)}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>{t('priority')}</Label>
                        <Select
                            value={form.data.priority}
                            onValueChange={(v) => form.setData('priority', v)}
                        >
                            <SelectTrigger>
                                <SelectValue />
                            </SelectTrigger>
                            <SelectContent>
                                {formOptions.priorities.map((v) => (
                                    <SelectItem key={v} value={v}>
                                        {priorityLabel(v)}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-2">
                        <Label>{t('openedAt')}</Label>
                        <Input
                            type="date"
                            value={form.data.opened_at}
                            onChange={(e) => form.setData('opened_at', e.target.value)}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>{t('targetCloseDate')}</Label>
                        <Input
                            type="date"
                            value={form.data.target_close_date}
                            onChange={(e) => form.setData('target_close_date', e.target.value)}
                        />
                        {form.errors.target_close_date && (
                            <p className="text-sm text-destructive">
                                {form.errors.target_close_date}
                            </p>
                        )}
                    </div>
                    <div className="space-y-2">
                        <Label>
                            {t('salaryMin')} ({form.data.currency || 'IDR'})
                        </Label>
                        <Input
                            type="number"
                            min={0}
                            value={form.data.salary_min}
                            onChange={(e) => form.setData('salary_min', e.target.value)}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>
                            {t('salaryMax')} ({form.data.currency || 'IDR'})
                        </Label>
                        <Input
                            type="number"
                            min={0}
                            value={form.data.salary_max}
                            onChange={(e) => form.setData('salary_max', e.target.value)}
                        />
                        {form.errors.salary_max && (
                            <p className="text-sm text-destructive">{form.errors.salary_max}</p>
                        )}
                    </div>
                </div>
            </section>

            <section className="space-y-3">
                <div className="text-xs font-medium uppercase text-muted-foreground">
                    {t('jobFormOwnership')}
                </div>
                <div className="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <div className="space-y-2">
                        <Label>{t('hiringManager')}</Label>
                        <Select
                            value={optionalValue(form.data.hiring_manager_id)}
                            onValueChange={(v) => setOptionalSelect('hiring_manager_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="—" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">—</SelectItem>
                                {formOptions.employees.map((e) => (
                                    <SelectItem key={e.id} value={String(e.id)}>
                                        {e.full_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                    <div className="space-y-2">
                        <Label>{t('recruiter')}</Label>
                        <Select
                            value={optionalValue(form.data.recruiter_id)}
                            onValueChange={(v) => setOptionalSelect('recruiter_id', v)}
                        >
                            <SelectTrigger>
                                <SelectValue placeholder="—" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="__none__">—</SelectItem>
                                {formOptions.employees.map((e) => (
                                    <SelectItem key={e.id} value={String(e.id)}>
                                        {e.full_name}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </div>
                </div>
            </section>

            <section className="space-y-3">
                <div className="text-xs font-medium uppercase text-muted-foreground">
                    {t('jobFormContent')}
                </div>
                <div className="space-y-3">
                    <div className="space-y-2">
                        <Label>{t('locationNote')}</Label>
                        <Input
                            value={form.data.location_note}
                            onChange={(e) => form.setData('location_note', e.target.value)}
                            placeholder="Hybrid (Jakarta), Remote, ..."
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>{t('description')}</Label>
                        <Textarea
                            rows={4}
                            value={form.data.description}
                            onChange={(e) => form.setData('description', e.target.value)}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>{t('requirements')}</Label>
                        <Textarea
                            rows={3}
                            value={form.data.requirements}
                            onChange={(e) => form.setData('requirements', e.target.value)}
                        />
                    </div>
                    <div className="space-y-2">
                        <Label>{t('benefits')}</Label>
                        <Textarea
                            rows={3}
                            value={form.data.benefits}
                            onChange={(e) => form.setData('benefits', e.target.value)}
                        />
                    </div>
                </div>
            </section>

            <div className="flex flex-wrap items-center justify-between gap-2">
                <div>
                    {showDelete && onDelete && (
                        <Button type="button" variant="destructive" onClick={onDelete}>
                            {t('delete')}
                        </Button>
                    )}
                </div>
                <div className="flex flex-wrap gap-2">
                    {onCancel && (
                        <Button type="button" variant="outline" onClick={onCancel}>
                            {t('cancel')}
                        </Button>
                    )}
                    <Button
                        type="button"
                        variant="secondary"
                        disabled={form.processing}
                        onClick={() => submit(false)}
                    >
                        {t('save')}
                    </Button>
                    {(mode === 'create' || form.data.status === 'draft') && (
                        <Button
                            type="button"
                            disabled={form.processing}
                            onClick={() => submit(true)}
                        >
                            {t('saveAndPublish')}
                        </Button>
                    )}
                </div>
            </div>
        </form>
    );
}
