import { useLanguage } from '@/i18n/LanguageContext';
import {
    CompanyOption,
    EmployeeDetail,
    ManagerOption,
    NamedOption,
    SalaryComponentOption,
    UserOption,
} from '@/Features/employees/types';
import {
    DOCUMENT_CATEGORIES,
    formatIdr,
    PTKP_OPTIONS,
    sanitizeDigits,
    TAX_METHOD_OPTIONS,
} from '@/Features/employees/format';
import { EmployeeStatusBadge } from '@/Features/employees/components/EmployeeStatusBadge';
import { EditEmployeeDialog } from '@/Features/employees/components/dialogs/EditEmployeeDialog';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import { Tabs, TabsContent, TabsList, TabsTrigger } from '@/Components/ui/tabs';
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
    DialogFooter,
    DialogHeader,
    DialogTitle,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Checkbox } from '@/Components/ui/checkbox';
import { RadioGroup, RadioGroupItem } from '@/Components/ui/radio-group';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Link, router, useForm } from '@inertiajs/react';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

type Props = {
    employee: EmployeeDetail;
    companies: CompanyOption[];
    statusOptions: string[];
    salaryComponents: SalaryComponentOption[];
    users: UserOption[];
    departments?: NamedOption[];
    positions?: NamedOption[];
    sites?: NamedOption[];
    managers?: ManagerOption[];
    attendanceSummary: Record<string, number>;
    payrollEstimate: {
        gross_allowances: number;
        total_deductions: number;
        estimated_net: number;
    };
};

const EMPLOYMENT_TYPES = ['pkwt', 'pkwtt', 'outsourcing', 'magang'];

function Field({ label, value }: { label: string; value: string | null | undefined }) {
    return (
        <div>
            <p className="text-xs text-muted-foreground">{label}</p>
            <p className="font-medium">{value || '—'}</p>
        </div>
    );
}

function asArray<T>(value: T[] | { data?: T[] } | null | undefined): T[] {
    if (Array.isArray(value)) {
        return value;
    }
    if (value && typeof value === 'object' && Array.isArray(value.data)) {
        return value.data;
    }

    return [];
}

export function EmployeeProfileTabs({
    employee: employeeProp,
    companies,
    statusOptions,
    salaryComponents,
    users,
    departments = [],
    positions = [],
    sites = [],
    managers = [],
    attendanceSummary,
    payrollEstimate,
}: Props) {
    const employee = {
        ...employeeProp,
        family_members: asArray(employeeProp.family_members),
        emergency_contacts: asArray(employeeProp.emergency_contacts),
        bank_accounts: asArray(employeeProp.bank_accounts),
        allowances: asArray(employeeProp.allowances),
        deductions: asArray(employeeProp.deductions),
        documents: asArray(employeeProp.documents),
        contracts: asArray(employeeProp.contracts),
        loans: asArray(employeeProp.loans),
        jobs: asArray(employeeProp.jobs),
        site_assignments: asArray(employeeProp.site_assignments),
        recent_payrolls: asArray(employeeProp.recent_payrolls),
        recent_attendances: asArray(employeeProp.recent_attendances),
    };
    const { t } = useLanguage();
    const [editOpen, setEditOpen] = useState(false);
    const [identityOpen, setIdentityOpen] = useState(false);
    const [taxOpen, setTaxOpen] = useState(false);
    const [allowanceOpen, setAllowanceOpen] = useState(false);
    const [deductionOpen, setDeductionOpen] = useState(false);
    const [familyOpen, setFamilyOpen] = useState(false);
    const [editingFamilyId, setEditingFamilyId] = useState<number | null>(null);
    const [bankOpen, setBankOpen] = useState(false);
    const [docOpen, setDocOpen] = useState(false);
    const [linkUserOpen, setLinkUserOpen] = useState(false);
    const [emergencyOpen, setEmergencyOpen] = useState(false);
    const [editingEmergencyId, setEditingEmergencyId] = useState<number | null>(null);
    const [loanOpen, setLoanOpen] = useState(false);
    const [editingLoanId, setEditingLoanId] = useState<number | null>(null);
    const [jobOpen, setJobOpen] = useState(false);
    const [editingJobId, setEditingJobId] = useState<number | null>(null);
    const [siteOpen, setSiteOpen] = useState(false);
    const [editingSiteId, setEditingSiteId] = useState<number | null>(null);

    const earningComponents = salaryComponents.filter((c) => c.type === 'earning');
    const deductionComponents = salaryComponents.filter((c) => c.type === 'deduction');

    const identityForm = useForm({
        nik: employee.identity?.nik ?? '',
        npwp: employee.identity?.npwp ?? '',
        bpjs_health: employee.identity?.bpjs_health ?? '',
        bpjs_employment: employee.identity?.bpjs_employment ?? '',
        address: employee.identity?.address ?? '',
        city: employee.identity?.city ?? '',
    });

    const taxForm = useForm({
        has_npwp: employee.tax_profile?.has_npwp ?? false,
        npwp: employee.tax_profile?.npwp ?? '',
        tax_status: employee.tax_profile?.tax_status ?? '',
        tax_method: employee.tax_profile?.tax_method ?? 'ter_monthly',
        dependents_count: employee.tax_profile?.dependents_count ?? 0,
    });

    const allowanceForm = useForm({
        component_id: '',
        name: '',
        amount: '',
        taxable: true,
        effective_start: '',
        effective_end: '',
        status: 'active',
        recurring: true,
    });

    const deductionForm = useForm({
        component_id: '',
        name: '',
        value: '',
        effective_start: '',
        effective_end: '',
        status: 'active',
        recurring: true,
    });

    const familyForm = useForm({
        name: '',
        relationship: 'spouse',
        birth_date: '',
        is_dependent: false,
    });

    const bankForm = useForm({
        bank_name: '',
        account_number: '',
        account_holder: '',
        is_primary: true,
    });

    const docForm = useForm<{ category: string; file: File | null }>({
        category: 'other',
        file: null,
    });

    const linkUserForm = useForm({
        user_id: employee.user_id ? String(employee.user_id) : '',
    });

    const emergencyForm = useForm({
        name: '',
        relationship: '',
        phone: '',
    });

    const loanForm = useForm({
        amount: '',
        remaining_amount: '',
        monthly_deduction: '',
    });

    const jobForm = useForm({
        company_id: '',
        department_id: '',
        position_id: '',
        manager_id: '',
        employment_type: '',
        start_date: '',
        end_date: '',
    });

    const siteForm = useForm({
        site_id: '',
        start_date: '',
        end_date: '',
    });

    const submitIdentity: FormEventHandler = (e) => {
        e.preventDefault();
        identityForm.post(route('employees.identity.store', employee.id), {
            preserveScroll: true,
            onSuccess: () => setIdentityOpen(false),
        });
    };

    const submitTax: FormEventHandler = (e) => {
        e.preventDefault();
        taxForm.post(route('employees.tax-profile.store', employee.id), {
            preserveScroll: true,
            onSuccess: () => setTaxOpen(false),
        });
    };

    const submitAllowance: FormEventHandler = (e) => {
        e.preventDefault();
        allowanceForm.post(route('employees.allowances.store', employee.id), {
            preserveScroll: true,
            onSuccess: () => {
                setAllowanceOpen(false);
                allowanceForm.reset();
            },
        });
    };

    const submitDeduction: FormEventHandler = (e) => {
        e.preventDefault();
        deductionForm.post(route('employees.deductions.store', employee.id), {
            preserveScroll: true,
            onSuccess: () => {
                setDeductionOpen(false);
                deductionForm.reset();
            },
        });
    };

    const openCreateFamily = () => {
        setEditingFamilyId(null);
        familyForm.reset();
        familyForm.clearErrors();
        setFamilyOpen(true);
    };

    const openEditFamily = (member: {
        id: number;
        name: string;
        relationship: string;
        birth_date: string | null;
        is_dependent: boolean;
    }) => {
        setEditingFamilyId(member.id);
        familyForm.clearErrors();
        familyForm.setData({
            name: member.name,
            relationship: member.relationship,
            birth_date: member.birth_date ?? '',
            is_dependent: member.is_dependent,
        });
        setFamilyOpen(true);
    };

    const submitFamily: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setFamilyOpen(false);
            setEditingFamilyId(null);
            familyForm.reset();
        };

        if (editingFamilyId === null) {
            familyForm.post(route('employees.family-members.store', employee.id), {
                preserveScroll: true,
                onSuccess,
            });
        } else {
            familyForm.put(
                route('employees.family-members.update', [employee.id, editingFamilyId]),
                { preserveScroll: true, onSuccess },
            );
        }
    };

    const deleteFamily = (id: number) => {
        if (!window.confirm('Delete this family member?')) return;
        router.delete(route('employees.family-members.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const submitBank: FormEventHandler = (e) => {
        e.preventDefault();
        bankForm.post(route('employees.bank-accounts.store', employee.id), {
            preserveScroll: true,
            onSuccess: () => {
                setBankOpen(false);
                bankForm.reset();
            },
        });
    };

    const submitDoc: FormEventHandler = (e) => {
        e.preventDefault();
        if (!docForm.data.file) return;
        router.post(
            route('employees.documents.store', employee.id),
            { category: docForm.data.category, file: docForm.data.file },
            { preserveScroll: true, forceFormData: true, onSuccess: () => setDocOpen(false) },
        );
    };

    const submitLinkUser: FormEventHandler = (e) => {
        e.preventDefault();
        linkUserForm.post(route('employees.link-user', employee.id), {
            preserveScroll: true,
            onSuccess: () => setLinkUserOpen(false),
        });
    };

    const deleteAllowance = (id: number) => {
        if (!window.confirm('Delete this item?')) return;
        router.delete(route('employees.allowances.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const deleteDeduction = (id: number) => {
        if (!window.confirm('Delete this item?')) return;
        router.delete(route('employees.deductions.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const deleteDocument = (id: number) => {
        if (!window.confirm('Delete this item?')) return;
        router.delete(route('employees.documents.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const openCreateEmergency = () => {
        setEditingEmergencyId(null);
        emergencyForm.reset();
        emergencyForm.clearErrors();
        setEmergencyOpen(true);
    };

    const openEditEmergency = (c: { id: number; name: string; relationship: string; phone: string }) => {
        setEditingEmergencyId(c.id);
        emergencyForm.clearErrors();
        emergencyForm.setData({ name: c.name, relationship: c.relationship, phone: c.phone });
        setEmergencyOpen(true);
    };

    const submitEmergency: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setEmergencyOpen(false);
            setEditingEmergencyId(null);
            emergencyForm.reset();
        };
        if (editingEmergencyId === null) {
            emergencyForm.post(route('employees.emergency-contacts.store', employee.id), {
                preserveScroll: true,
                onSuccess,
            });
        } else {
            emergencyForm.put(
                route('employees.emergency-contacts.update', [employee.id, editingEmergencyId]),
                { preserveScroll: true, onSuccess },
            );
        }
    };

    const deleteEmergency = (id: number) => {
        if (!window.confirm('Delete this contact?')) return;
        router.delete(route('employees.emergency-contacts.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const openCreateLoan = () => {
        setEditingLoanId(null);
        loanForm.reset();
        loanForm.clearErrors();
        setLoanOpen(true);
    };

    const openEditLoan = (l: { id: number; amount: string; remaining_amount: string; monthly_deduction: string }) => {
        setEditingLoanId(l.id);
        loanForm.clearErrors();
        loanForm.setData({
            amount: String(l.amount),
            remaining_amount: String(l.remaining_amount),
            monthly_deduction: String(l.monthly_deduction),
        });
        setLoanOpen(true);
    };

    const submitLoan: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setLoanOpen(false);
            setEditingLoanId(null);
            loanForm.reset();
        };
        if (editingLoanId === null) {
            loanForm.post(route('employees.loans.store', employee.id), {
                preserveScroll: true,
                onSuccess,
            });
        } else {
            loanForm.put(route('employees.loans.update', [employee.id, editingLoanId]), {
                preserveScroll: true,
                onSuccess,
            });
        }
    };

    const deleteLoan = (id: number) => {
        if (!window.confirm('Delete this loan?')) return;
        router.delete(route('employees.loans.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const openCreateJob = () => {
        setEditingJobId(null);
        jobForm.reset();
        jobForm.clearErrors();
        setJobOpen(true);
    };

    const openEditJob = (j: {
        id: number;
        company_id: number;
        department_id: number | null;
        position_id: number | null;
        manager_id: number | null;
        employment_type: string | null;
        start_date: string | null;
        end_date: string | null;
    }) => {
        setEditingJobId(j.id);
        jobForm.clearErrors();
        jobForm.setData({
            company_id: String(j.company_id),
            department_id: j.department_id ? String(j.department_id) : '',
            position_id: j.position_id ? String(j.position_id) : '',
            manager_id: j.manager_id ? String(j.manager_id) : '',
            employment_type: j.employment_type ?? '',
            start_date: j.start_date ?? '',
            end_date: j.end_date ?? '',
        });
        setJobOpen(true);
    };

    const submitJob: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setJobOpen(false);
            setEditingJobId(null);
            jobForm.reset();
        };
        jobForm.transform((data) => ({
            ...data,
            department_id: data.department_id || null,
            position_id: data.position_id || null,
            manager_id: data.manager_id || null,
            employment_type: data.employment_type || null,
            end_date: data.end_date || null,
        }));
        if (editingJobId === null) {
            jobForm.post(route('employees.jobs.store', employee.id), {
                preserveScroll: true,
                onSuccess,
            });
        } else {
            jobForm.put(route('employees.jobs.update', [employee.id, editingJobId]), {
                preserveScroll: true,
                onSuccess,
            });
        }
    };

    const deleteJob = (id: number) => {
        if (!window.confirm('Delete this job history?')) return;
        router.delete(route('employees.jobs.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const openCreateSite = () => {
        setEditingSiteId(null);
        siteForm.reset();
        siteForm.clearErrors();
        setSiteOpen(true);
    };

    const openEditSite = (s: { id: number; site_id: number; start_date: string | null; end_date: string | null }) => {
        setEditingSiteId(s.id);
        siteForm.clearErrors();
        siteForm.setData({
            site_id: String(s.site_id),
            start_date: s.start_date ?? '',
            end_date: s.end_date ?? '',
        });
        setSiteOpen(true);
    };

    const submitSite: FormEventHandler = (e) => {
        e.preventDefault();
        const onSuccess = () => {
            setSiteOpen(false);
            setEditingSiteId(null);
            siteForm.reset();
        };
        siteForm.transform((data) => ({
            ...data,
            end_date: data.end_date || null,
        }));
        if (editingSiteId === null) {
            siteForm.post(route('employees.site-assignments.store', employee.id), {
                preserveScroll: true,
                onSuccess,
            });
        } else {
            siteForm.put(
                route('employees.site-assignments.update', [employee.id, editingSiteId]),
                { preserveScroll: true, onSuccess },
            );
        }
    };

    const deleteSite = (id: number) => {
        if (!window.confirm('Delete this site assignment?')) return;
        router.delete(route('employees.site-assignments.destroy', [employee.id, id]), {
            preserveScroll: true,
        });
    };

    const pickComponent = (
        componentId: string,
        type: 'earning' | 'deduction',
        setName: (n: string) => void,
        setAmount: (a: string) => void,
    ) => {
        const list = type === 'earning' ? earningComponents : deductionComponents;
        const c = list.find((x) => String(x.id) === componentId);
        if (c) {
            setName(c.name);
            setAmount(String(c.default_value));
        }
    };

    return (
        <>
            <Tabs defaultValue="personal" className="space-y-4">
                <TabsList className="flex flex-wrap h-auto">
                    <TabsTrigger value="personal">{t('personalInfo')}</TabsTrigger>
                    <TabsTrigger value="family">{t('family')}</TabsTrigger>
                    <TabsTrigger value="employment">{t('employment')}</TabsTrigger>
                    <TabsTrigger value="payroll">{t('payroll')}</TabsTrigger>
                    <TabsTrigger value="documents">{t('documents')}</TabsTrigger>
                </TabsList>

                <TabsContent value="personal" className="space-y-4">
                    <div className="flex flex-wrap gap-2">
                        <Button size="sm" onClick={() => setEditOpen(true)}>
                            <Pencil className="mr-2 h-4 w-4" />
                            {t('edit')}
                        </Button>
                        <Button size="sm" variant="outline" onClick={() => setIdentityOpen(true)}>
                            {t('idNumber')} / NPWP
                        </Button>
                        <Button size="sm" variant="outline" onClick={() => setLinkUserOpen(true)}>
                            Link user
                        </Button>
                    </div>
                    <Card>
                        <CardContent className="grid gap-4 p-6 sm:grid-cols-2 lg:grid-cols-3">
                            <Field label={t('employeeCode')} value={employee.employee_code} />
                            <Field label={t('name')} value={employee.full_name} />
                            <Field label={t('email')} value={employee.email} />
                            <Field label={t('phone')} value={employee.phone} />
                            <Field label={t('gender')} value={employee.gender} />
                            <Field label={t('birthDate')} value={employee.birth_date} />
                            <Field label={t('religion')} value={employee.religion} />
                            <Field label={t('maritalStatus')} value={employee.marital_status} />
                            <div>
                                <p className="text-xs text-muted-foreground">{t('status')}</p>
                                <EmployeeStatusBadge status={employee.status} />
                            </div>
                            {employee.identity && (
                                <>
                                    <Field label={t('idNumber')} value={employee.identity.nik} />
                                    <Field label="NPWP" value={employee.identity.npwp} />
                                    <Field label="BPJS Kes" value={employee.identity.bpjs_health} />
                                    <Field label="BPJS TK" value={employee.identity.bpjs_employment} />
                                </>
                            )}
                            {employee.user && (
                                <Field label="Linked user" value={`${employee.user.name} (${employee.user.email})`} />
                            )}
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="family" className="space-y-4">
                    <Button size="sm" onClick={openCreateFamily}>
                        <Plus className="mr-2 h-4 w-4" />
                        Add family member
                    </Button>
                    <Card>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('name')}</TableHead>
                                        <TableHead>Relationship</TableHead>
                                        <TableHead>{t('birthDate')}</TableHead>
                                        <TableHead>Dependent</TableHead>
                                        <TableHead className="text-right">{t('actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.family_members.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={5} className="text-center text-muted-foreground">
                                                {t('noData')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        employee.family_members.map((m) => (
                                            <TableRow key={m.id}>
                                                <TableCell>{m.name}</TableCell>
                                                <TableCell>{m.relationship}</TableCell>
                                                <TableCell>{m.birth_date ?? '—'}</TableCell>
                                                <TableCell>
                                                    <Badge variant={m.is_dependent ? 'default' : 'secondary'}>
                                                        {m.is_dependent ? t('yes') : t('no')}
                                                    </Badge>
                                                </TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-1">
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            aria-label={t('edit')}
                                                            onClick={() => openEditFamily(m)}
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            aria-label={t('delete')}
                                                            onClick={() => deleteFamily(m.id)}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
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
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-base">{t('emergencyContacts')}</CardTitle>
                            <Button size="sm" variant="outline" onClick={openCreateEmergency}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addEmergencyContact')}
                            </Button>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('name')}</TableHead>
                                        <TableHead>{t('relationship')}</TableHead>
                                        <TableHead>{t('phone')}</TableHead>
                                        <TableHead className="text-right">{t('actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.emergency_contacts.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center text-muted-foreground">
                                                {t('noData')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        employee.emergency_contacts.map((c) => (
                                            <TableRow key={c.id}>
                                                <TableCell>{c.name}</TableCell>
                                                <TableCell>{c.relationship}</TableCell>
                                                <TableCell>{c.phone}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-1">
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            aria-label={t('edit')}
                                                            onClick={() => openEditEmergency(c)}
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>
                                                        <Button
                                                            size="icon"
                                                            variant="ghost"
                                                            aria-label={t('delete')}
                                                            onClick={() => deleteEmergency(c.id)}
                                                        >
                                                            <Trash2 className="h-4 w-4" />
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
                </TabsContent>

                <TabsContent value="employment" className="space-y-4">
                    <Card>
                        <CardContent className="grid gap-4 p-6 sm:grid-cols-2">
                            <Field label={t('company')} value={employee.company_name} />
                            <Field label={t('site')} value={employee.site_name} />
                            <Field label={t('department')} value={employee.department_name} />
                            <Field label={t('position')} value={employee.position_name} />
                            <Field label="Manager" value={employee.manager_name} />
                            <Field label={t('joinDate')} value={employee.join_date} />
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-base">{t('jobHistory')}</CardTitle>
                            <Button size="sm" variant="outline" onClick={openCreateJob}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addJobHistory')}
                            </Button>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('company')}</TableHead>
                                        <TableHead>{t('department')}</TableHead>
                                        <TableHead>{t('position')}</TableHead>
                                        <TableHead>{t('employmentType')}</TableHead>
                                        <TableHead>{t('startDate')}</TableHead>
                                        <TableHead>{t('endDate')}</TableHead>
                                        <TableHead className="text-right">{t('actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.jobs.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={7} className="text-center text-muted-foreground">
                                                {t('noData')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        employee.jobs.map((j) => (
                                            <TableRow key={j.id}>
                                                <TableCell>{j.company_name ?? '—'}</TableCell>
                                                <TableCell>{j.department_name ?? '—'}</TableCell>
                                                <TableCell>{j.position_name ?? '—'}</TableCell>
                                                <TableCell>{j.employment_type ?? '—'}</TableCell>
                                                <TableCell>{j.start_date ?? '—'}</TableCell>
                                                <TableCell>{j.end_date ?? '—'}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-1">
                                                        <Button size="icon" variant="ghost" aria-label={t('edit')} onClick={() => openEditJob(j)}>
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>
                                                        <Button size="icon" variant="ghost" aria-label={t('delete')} onClick={() => deleteJob(j.id)}>
                                                            <Trash2 className="h-4 w-4" />
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

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-base">{t('siteAssignments')}</CardTitle>
                            <Button size="sm" variant="outline" onClick={openCreateSite}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addSiteAssignment')}
                            </Button>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('site')}</TableHead>
                                        <TableHead>{t('startDate')}</TableHead>
                                        <TableHead>{t('endDate')}</TableHead>
                                        <TableHead className="text-right">{t('actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.site_assignments.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center text-muted-foreground">
                                                {t('noData')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        employee.site_assignments.map((s) => (
                                            <TableRow key={s.id}>
                                                <TableCell>{s.site_name ?? '—'}</TableCell>
                                                <TableCell>{s.start_date ?? '—'}</TableCell>
                                                <TableCell>{s.end_date ?? '—'}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-1">
                                                        <Button size="icon" variant="ghost" aria-label={t('edit')} onClick={() => openEditSite(s)}>
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>
                                                        <Button size="icon" variant="ghost" aria-label={t('delete')} onClick={() => deleteSite(s.id)}>
                                                            <Trash2 className="h-4 w-4" />
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

                    <Button variant="outline" size="sm" asChild>
                        <Link href={route('contracts.index')}>{t('contracts')}</Link>
                    </Button>
                    {employee.contracts.length > 0 && (
                        <Card>
                            <CardHeader>
                                <CardTitle className="text-base">{t('contracts')}</CardTitle>
                            </CardHeader>
                            <CardContent className="p-0">
                                <Table>
                                    <TableHeader>
                                        <TableRow>
                                            <TableHead>Type</TableHead>
                                            <TableHead>Start</TableHead>
                                            <TableHead>End</TableHead>
                                        </TableRow>
                                    </TableHeader>
                                    <TableBody>
                                        {employee.contracts.map((c) => (
                                            <TableRow key={c.id}>
                                                <TableCell>{c.contract_type}</TableCell>
                                                <TableCell>{c.start_date}</TableCell>
                                                <TableCell>{c.end_date ?? '—'}</TableCell>
                                            </TableRow>
                                        ))}
                                    </TableBody>
                                </Table>
                            </CardContent>
                        </Card>
                    )}
                </TabsContent>

                <TabsContent value="payroll" className="space-y-4">
                    <div className="grid gap-4 md:grid-cols-3">
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm text-muted-foreground">Gross allowances</CardTitle>
                            </CardHeader>
                            <CardContent className="text-xl font-bold">
                                {formatIdr(payrollEstimate.gross_allowances)}
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm text-muted-foreground">Deductions</CardTitle>
                            </CardHeader>
                            <CardContent className="text-xl font-bold">
                                {formatIdr(payrollEstimate.total_deductions)}
                            </CardContent>
                        </Card>
                        <Card>
                            <CardHeader className="pb-2">
                                <CardTitle className="text-sm text-muted-foreground">Est. net</CardTitle>
                            </CardHeader>
                            <CardContent className="text-xl font-bold">
                                {formatIdr(payrollEstimate.estimated_net)}
                            </CardContent>
                        </Card>
                    </div>

                    <div className="flex flex-wrap gap-2">
                        <Button size="sm" onClick={() => setTaxOpen(true)}>
                            Tax profile (PPh21)
                        </Button>
                        <Button size="sm" variant="outline" onClick={() => setBankOpen(true)}>
                            Bank account
                        </Button>
                        <Button size="sm" variant="outline" onClick={() => setAllowanceOpen(true)}>
                            <Plus className="mr-1 h-4 w-4" />
                            Allowance
                        </Button>
                        <Button size="sm" variant="outline" onClick={() => setDeductionOpen(true)}>
                            <Plus className="mr-1 h-4 w-4" />
                            Deduction
                        </Button>
                    </div>

                    {employee.tax_profile && (
                        <Card>
                            <CardContent className="grid gap-4 p-6 sm:grid-cols-3">
                                <Field label="NPWP" value={employee.tax_profile.npwp} />
                                <Field label="PTKP" value={employee.tax_profile.tax_status} />
                                <Field label="Tax method" value={employee.tax_profile.tax_method} />
                            </CardContent>
                        </Card>
                    )}

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Allowances</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('name')}</TableHead>
                                        <TableHead>Amount</TableHead>
                                        <TableHead>{t('status')}</TableHead>
                                        <TableHead />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.allowances.map((a) => (
                                        <TableRow key={a.id}>
                                            <TableCell>{a.name}</TableCell>
                                            <TableCell>{formatIdr(a.amount)}</TableCell>
                                            <TableCell>{a.status}</TableCell>
                                            <TableCell>
                                                <Button variant="ghost" size="icon" onClick={() => deleteAllowance(a.id)}>
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">Deductions</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('name')}</TableHead>
                                        <TableHead>Value</TableHead>
                                        <TableHead>{t('status')}</TableHead>
                                        <TableHead />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.deductions.map((d) => (
                                        <TableRow key={d.id}>
                                            <TableCell>{d.name}</TableCell>
                                            <TableCell>{formatIdr(d.value)}</TableCell>
                                            <TableCell>{d.status}</TableCell>
                                            <TableCell>
                                                <Button variant="ghost" size="icon" onClick={() => deleteDeduction(d.id)}>
                                                    <Trash2 className="h-4 w-4" />
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader className="flex flex-row items-center justify-between">
                            <CardTitle className="text-base">{t('loans')}</CardTitle>
                            <Button size="sm" variant="outline" onClick={openCreateLoan}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addLoan')}
                            </Button>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('loanAmount')}</TableHead>
                                        <TableHead>{t('remaining')}</TableHead>
                                        <TableHead>{t('monthlyDeduction')}</TableHead>
                                        <TableHead className="text-right">{t('actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.loans.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={4} className="text-center text-muted-foreground">
                                                {t('noData')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        employee.loans.map((l) => (
                                            <TableRow key={l.id}>
                                                <TableCell>{formatIdr(l.amount)}</TableCell>
                                                <TableCell>{formatIdr(l.remaining_amount)}</TableCell>
                                                <TableCell>{formatIdr(l.monthly_deduction)}</TableCell>
                                                <TableCell className="text-right">
                                                    <div className="flex justify-end gap-1">
                                                        <Button size="icon" variant="ghost" aria-label={t('edit')} onClick={() => openEditLoan(l)}>
                                                            <Pencil className="h-4 w-4" />
                                                        </Button>
                                                        <Button size="icon" variant="ghost" aria-label={t('delete')} onClick={() => deleteLoan(l.id)}>
                                                            <Trash2 className="h-4 w-4" />
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

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('attendance')} — this month</CardTitle>
                        </CardHeader>
                        <CardContent className="grid grid-cols-3 gap-4 text-center">
                            <div>
                                <p className="text-2xl font-bold">{attendanceSummary.attendance_days}</p>
                                <p className="text-xs text-muted-foreground">Present</p>
                            </div>
                            <div>
                                <p className="text-2xl font-bold">{attendanceSummary.leave_days}</p>
                                <p className="text-xs text-muted-foreground">Leave</p>
                            </div>
                            <div>
                                <p className="text-2xl font-bold">{attendanceSummary.absent_days}</p>
                                <p className="text-xs text-muted-foreground">Absent</p>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle className="text-base">{t('payroll')}</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Period</TableHead>
                                        <TableHead>Net</TableHead>
                                        <TableHead>{t('status')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.recent_payrolls.map((p) => (
                                        <TableRow key={p.id}>
                                            <TableCell>
                                                {p.period_month}/{p.period_year}
                                            </TableCell>
                                            <TableCell>{formatIdr(p.net_salary)}</TableCell>
                                            <TableCell>
                                                <Badge variant="secondary">{p.status}</Badge>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>

                <TabsContent value="documents" className="space-y-4">
                    <Button size="sm" onClick={() => setDocOpen(true)}>
                        <Plus className="mr-2 h-4 w-4" />
                        Upload document
                    </Button>
                    <Card>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>Category</TableHead>
                                        <TableHead>File</TableHead>
                                        <TableHead />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {employee.documents.length === 0 ? (
                                        <TableRow>
                                            <TableCell colSpan={3} className="text-center text-muted-foreground">
                                                {t('noData')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        employee.documents.map((doc) => (
                                            <TableRow key={doc.id}>
                                                <TableCell>{doc.category}</TableCell>
                                                <TableCell>
                                                    <a href={doc.url} target="_blank" rel="noreferrer" className="text-primary underline">
                                                        {doc.original_name}
                                                    </a>
                                                </TableCell>
                                                <TableCell>
                                                    <Button variant="ghost" size="icon" onClick={() => deleteDocument(doc.id)}>
                                                        <Trash2 className="h-4 w-4" />
                                                    </Button>
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                </TabsContent>
            </Tabs>

            <EditEmployeeDialog
                employee={employee}
                companies={companies}
                statusOptions={statusOptions}
                open={editOpen}
                onOpenChange={setEditOpen}
            />

            <Dialog open={identityOpen} onOpenChange={setIdentityOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{t('idNumber')} / NPWP / BPJS</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitIdentity} className="space-y-3">
                        <div className="space-y-2">
                            <Label>NIK (16 digit)</Label>
                            <Input
                                value={identityForm.data.nik}
                                onChange={(e) => identityForm.setData('nik', sanitizeDigits(e.target.value, 16))}
                                inputMode="numeric"
                                pattern="\d*"
                                maxLength={16}
                                placeholder="16 digit angka"
                            />
                            {identityForm.data.nik.length > 0 && identityForm.data.nik.length < 16 && (
                                <p className="text-sm text-destructive">NIK harus 16 digit angka.</p>
                            )}
                            {identityForm.errors.nik && (
                                <p className="text-sm text-destructive">{identityForm.errors.nik}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label>NPWP</Label>
                            <Input value={identityForm.data.npwp} onChange={(e) => identityForm.setData('npwp', e.target.value)} />
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label>BPJS Kesehatan</Label>
                                <Input value={identityForm.data.bpjs_health} onChange={(e) => identityForm.setData('bpjs_health', e.target.value)} />
                            </div>
                            <div className="space-y-2">
                                <Label>BPJS Ketenagakerjaan</Label>
                                <Input value={identityForm.data.bpjs_employment} onChange={(e) => identityForm.setData('bpjs_employment', e.target.value)} />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={identityForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={taxOpen} onOpenChange={setTaxOpen}>
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>Tax profile (PPh21 / TER)</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitTax} className="space-y-3">
                        <div className="flex items-center gap-2">
                            <Checkbox checked={taxForm.data.has_npwp} onCheckedChange={(v) => taxForm.setData('has_npwp', !!v)} />
                            <Label>Has NPWP</Label>
                        </div>
                        <div className="space-y-2">
                            <Label>NPWP</Label>
                            <Input value={taxForm.data.npwp} onChange={(e) => taxForm.setData('npwp', e.target.value)} />
                        </div>
                        <div className="space-y-2">
                            <Label>PTKP (tax_status)</Label>
                            <Select value={taxForm.data.tax_status || '_none'} onValueChange={(v) => taxForm.setData('tax_status', v === '_none' ? '' : v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_none">—</SelectItem>
                                    {PTKP_OPTIONS.map((o) => (
                                        <SelectItem key={o} value={o}>{o}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>Tax method</Label>
                            <Select value={taxForm.data.tax_method} onValueChange={(v) => taxForm.setData('tax_method', v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    {TAX_METHOD_OPTIONS.map((o) => (
                                        <SelectItem key={o.value} value={o.value}>{o.value}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={taxForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={allowanceOpen} onOpenChange={setAllowanceOpen}>
                <DialogContent>
                    <DialogHeader><DialogTitle>Allowance</DialogTitle></DialogHeader>
                    <form onSubmit={submitAllowance} className="space-y-3">
                        <div className="space-y-2">
                            <Label>Catalog</Label>
                            <Select value={allowanceForm.data.component_id} onValueChange={(v) => {
                                allowanceForm.setData('component_id', v);
                                pickComponent(v, 'earning', (n) => allowanceForm.setData('name', n), (a) => allowanceForm.setData('amount', a));
                            }}>
                                <SelectTrigger><SelectValue placeholder="Select" /></SelectTrigger>
                                <SelectContent>
                                    {earningComponents.map((c) => (
                                        <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('name')}</Label>
                            <Input value={allowanceForm.data.name} onChange={(e) => allowanceForm.setData('name', e.target.value)} required />
                        </div>
                        <div className="space-y-2">
                            <Label>Amount (IDR)</Label>
                            <Input type="number" value={allowanceForm.data.amount} onChange={(e) => allowanceForm.setData('amount', e.target.value)} required />
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={allowanceForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={deductionOpen} onOpenChange={setDeductionOpen}>
                <DialogContent>
                    <DialogHeader><DialogTitle>Deduction</DialogTitle></DialogHeader>
                    <form onSubmit={submitDeduction} className="space-y-3">
                        <div className="space-y-2">
                            <Label>Catalog</Label>
                            <Select value={deductionForm.data.component_id} onValueChange={(v) => {
                                deductionForm.setData('component_id', v);
                                pickComponent(v, 'deduction', (n) => deductionForm.setData('name', n), (a) => deductionForm.setData('value', a));
                            }}>
                                <SelectTrigger><SelectValue placeholder="Select" /></SelectTrigger>
                                <SelectContent>
                                    {deductionComponents.map((c) => (
                                        <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('name')}</Label>
                            <Input value={deductionForm.data.name} onChange={(e) => deductionForm.setData('name', e.target.value)} required />
                        </div>
                        <div className="space-y-2">
                            <Label>Value</Label>
                            <Input type="number" value={deductionForm.data.value} onChange={(e) => deductionForm.setData('value', e.target.value)} required />
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={deductionForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={familyOpen}
                onOpenChange={(open) => {
                    setFamilyOpen(open);
                    if (!open) {
                        setEditingFamilyId(null);
                        familyForm.reset();
                        familyForm.clearErrors();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {editingFamilyId === null ? 'Add family member' : 'Edit family member'}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitFamily} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('name')}</Label>
                            <Input value={familyForm.data.name} onChange={(e) => familyForm.setData('name', e.target.value)} required />
                            {familyForm.errors.name && (
                                <p className="text-sm text-destructive">{familyForm.errors.name}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label>Relationship</Label>
                            <Input value={familyForm.data.relationship} onChange={(e) => familyForm.setData('relationship', e.target.value)} required />
                            {familyForm.errors.relationship && (
                                <p className="text-sm text-destructive">{familyForm.errors.relationship}</p>
                            )}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('birthDate')}</Label>
                            <Input
                                type="date"
                                value={familyForm.data.birth_date}
                                onChange={(e) => familyForm.setData('birth_date', e.target.value)}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>Dependent</Label>
                            <RadioGroup
                                className="flex gap-6"
                                value={familyForm.data.is_dependent ? 'yes' : 'no'}
                                onValueChange={(v) => familyForm.setData('is_dependent', v === 'yes')}
                            >
                                <div className="flex items-center gap-2">
                                    <RadioGroupItem value="yes" id="family-dependent-yes" />
                                    <Label htmlFor="family-dependent-yes" className="font-normal">{t('yes')}</Label>
                                </div>
                                <div className="flex items-center gap-2">
                                    <RadioGroupItem value="no" id="family-dependent-no" />
                                    <Label htmlFor="family-dependent-no" className="font-normal">{t('no')}</Label>
                                </div>
                            </RadioGroup>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={familyForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={bankOpen} onOpenChange={setBankOpen}>
                <DialogContent>
                    <DialogHeader><DialogTitle>Bank account</DialogTitle></DialogHeader>
                    <form onSubmit={submitBank} className="space-y-3">
                        <div className="space-y-2">
                            <Label>Bank</Label>
                            <Input value={bankForm.data.bank_name} onChange={(e) => bankForm.setData('bank_name', e.target.value)} required />
                        </div>
                        <div className="space-y-2">
                            <Label>Account number</Label>
                            <Input value={bankForm.data.account_number} onChange={(e) => bankForm.setData('account_number', e.target.value)} required />
                        </div>
                        <div className="space-y-2">
                            <Label>Account holder</Label>
                            <Input value={bankForm.data.account_holder} onChange={(e) => bankForm.setData('account_holder', e.target.value)} required />
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={bankForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={docOpen} onOpenChange={setDocOpen}>
                <DialogContent>
                    <DialogHeader><DialogTitle>Upload document</DialogTitle></DialogHeader>
                    <form onSubmit={submitDoc} className="space-y-3">
                        <div className="space-y-2">
                            <Label>Category</Label>
                            <Select value={docForm.data.category} onValueChange={(v) => docForm.setData('category', v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    {DOCUMENT_CATEGORIES.map((c) => (
                                        <SelectItem key={c} value={c}>{c}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>File</Label>
                            <Input type="file" onChange={(e) => docForm.setData('file', e.target.files?.[0] ?? null)} />
                        </div>
                        <DialogFooter>
                            <Button type="submit">{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog open={linkUserOpen} onOpenChange={setLinkUserOpen}>
                <DialogContent>
                    <DialogHeader><DialogTitle>Link user account</DialogTitle></DialogHeader>
                    <form onSubmit={submitLinkUser} className="space-y-3">
                        <div className="space-y-2">
                            <Label>User</Label>
                            <Select value={linkUserForm.data.user_id || '_none'} onValueChange={(v) => linkUserForm.setData('user_id', v === '_none' ? '' : v)}>
                                <SelectTrigger><SelectValue /></SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="_none">None</SelectItem>
                                    {users.map((u) => (
                                        <SelectItem key={u.id} value={String(u.id)}>{u.name} ({u.email})</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={linkUserForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={emergencyOpen}
                onOpenChange={(open) => {
                    setEmergencyOpen(open);
                    if (!open) {
                        setEditingEmergencyId(null);
                        emergencyForm.reset();
                        emergencyForm.clearErrors();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>
                            {editingEmergencyId === null ? t('addEmergencyContact') : t('emergencyContacts')}
                        </DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitEmergency} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('name')}</Label>
                            <Input value={emergencyForm.data.name} onChange={(e) => emergencyForm.setData('name', e.target.value)} required />
                            {emergencyForm.errors.name && <p className="text-sm text-destructive">{emergencyForm.errors.name}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('relationship')}</Label>
                            <Input value={emergencyForm.data.relationship} onChange={(e) => emergencyForm.setData('relationship', e.target.value)} required />
                            {emergencyForm.errors.relationship && <p className="text-sm text-destructive">{emergencyForm.errors.relationship}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('phone')}</Label>
                            <Input value={emergencyForm.data.phone} onChange={(e) => emergencyForm.setData('phone', e.target.value)} required />
                            {emergencyForm.errors.phone && <p className="text-sm text-destructive">{emergencyForm.errors.phone}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={emergencyForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={loanOpen}
                onOpenChange={(open) => {
                    setLoanOpen(open);
                    if (!open) {
                        setEditingLoanId(null);
                        loanForm.reset();
                        loanForm.clearErrors();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editingLoanId === null ? t('addLoan') : t('loans')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitLoan} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('loanAmount')} (IDR)</Label>
                            <Input type="number" value={loanForm.data.amount} onChange={(e) => loanForm.setData('amount', e.target.value)} required />
                            {loanForm.errors.amount && <p className="text-sm text-destructive">{loanForm.errors.amount}</p>}
                        </div>
                        <div className="space-y-2">
                            <Label>{t('remaining')} (IDR)</Label>
                            <Input type="number" value={loanForm.data.remaining_amount} onChange={(e) => loanForm.setData('remaining_amount', e.target.value)} placeholder={loanForm.data.amount} />
                        </div>
                        <div className="space-y-2">
                            <Label>{t('monthlyDeduction')} (IDR)</Label>
                            <Input type="number" value={loanForm.data.monthly_deduction} onChange={(e) => loanForm.setData('monthly_deduction', e.target.value)} required />
                            {loanForm.errors.monthly_deduction && <p className="text-sm text-destructive">{loanForm.errors.monthly_deduction}</p>}
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={loanForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={jobOpen}
                onOpenChange={(open) => {
                    setJobOpen(open);
                    if (!open) {
                        setEditingJobId(null);
                        jobForm.reset();
                        jobForm.clearErrors();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editingJobId === null ? t('addJobHistory') : t('jobHistory')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitJob} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('company')}</Label>
                            <Select value={jobForm.data.company_id} onValueChange={(v) => jobForm.setData('company_id', v)}>
                                <SelectTrigger><SelectValue placeholder={t('company')} /></SelectTrigger>
                                <SelectContent>
                                    {companies.map((c) => (
                                        <SelectItem key={c.id} value={String(c.id)}>{c.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {jobForm.errors.company_id && <p className="text-sm text-destructive">{jobForm.errors.company_id}</p>}
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label>{t('department')}</Label>
                                <Select value={jobForm.data.department_id || '_none'} onValueChange={(v) => jobForm.setData('department_id', v === '_none' ? '' : v)}>
                                    <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="_none">—</SelectItem>
                                        {departments.map((d) => (
                                            <SelectItem key={d.id} value={String(d.id)}>{d.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label>{t('position')}</Label>
                                <Select value={jobForm.data.position_id || '_none'} onValueChange={(v) => jobForm.setData('position_id', v === '_none' ? '' : v)}>
                                    <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="_none">—</SelectItem>
                                        {positions.map((p) => (
                                            <SelectItem key={p.id} value={String(p.id)}>{p.name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label>{t('manager')}</Label>
                                <Select value={jobForm.data.manager_id || '_none'} onValueChange={(v) => jobForm.setData('manager_id', v === '_none' ? '' : v)}>
                                    <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="_none">—</SelectItem>
                                        {managers.map((m) => (
                                            <SelectItem key={m.id} value={String(m.id)}>{m.full_name}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                            <div className="space-y-2">
                                <Label>{t('employmentType')}</Label>
                                <Select value={jobForm.data.employment_type || '_none'} onValueChange={(v) => jobForm.setData('employment_type', v === '_none' ? '' : v)}>
                                    <SelectTrigger><SelectValue placeholder="—" /></SelectTrigger>
                                    <SelectContent>
                                        <SelectItem value="_none">—</SelectItem>
                                        {EMPLOYMENT_TYPES.map((tp) => (
                                            <SelectItem key={tp} value={tp}>{tp}</SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label>{t('startDate')}</Label>
                                <Input type="date" value={jobForm.data.start_date} onChange={(e) => jobForm.setData('start_date', e.target.value)} required />
                                {jobForm.errors.start_date && <p className="text-sm text-destructive">{jobForm.errors.start_date}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label>{t('endDate')}</Label>
                                <Input type="date" value={jobForm.data.end_date} onChange={(e) => jobForm.setData('end_date', e.target.value)} />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={jobForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>

            <Dialog
                open={siteOpen}
                onOpenChange={(open) => {
                    setSiteOpen(open);
                    if (!open) {
                        setEditingSiteId(null);
                        siteForm.reset();
                        siteForm.clearErrors();
                    }
                }}
            >
                <DialogContent>
                    <DialogHeader>
                        <DialogTitle>{editingSiteId === null ? t('addSiteAssignment') : t('siteAssignments')}</DialogTitle>
                    </DialogHeader>
                    <form onSubmit={submitSite} className="space-y-3">
                        <div className="space-y-2">
                            <Label>{t('site')}</Label>
                            <Select value={siteForm.data.site_id} onValueChange={(v) => siteForm.setData('site_id', v)}>
                                <SelectTrigger><SelectValue placeholder={t('site')} /></SelectTrigger>
                                <SelectContent>
                                    {sites.map((s) => (
                                        <SelectItem key={s.id} value={String(s.id)}>{s.name}</SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                            {siteForm.errors.site_id && <p className="text-sm text-destructive">{siteForm.errors.site_id}</p>}
                        </div>
                        <div className="grid grid-cols-2 gap-3">
                            <div className="space-y-2">
                                <Label>{t('startDate')}</Label>
                                <Input type="date" value={siteForm.data.start_date} onChange={(e) => siteForm.setData('start_date', e.target.value)} required />
                                {siteForm.errors.start_date && <p className="text-sm text-destructive">{siteForm.errors.start_date}</p>}
                            </div>
                            <div className="space-y-2">
                                <Label>{t('endDate')}</Label>
                                <Input type="date" value={siteForm.data.end_date} onChange={(e) => siteForm.setData('end_date', e.target.value)} />
                            </div>
                        </div>
                        <DialogFooter>
                            <Button type="submit" disabled={siteForm.processing}>{t('save')}</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </>
    );
}
