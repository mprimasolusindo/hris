export type SalaryComponentOption = {
    id: number;
    name: string;
    code: string | null;
    type: 'earning' | 'deduction';
    calculation_method: string;
    default_value: string;
    is_taxable: boolean;
};

export type EmployeeIdentity = {
    id: number;
    nik: string | null;
    npwp: string | null;
    bpjs_health: string | null;
    bpjs_employment: string | null;
    address: string | null;
    city: string | null;
} | null;

export type EmployeeTaxProfile = {
    id: number;
    has_npwp: boolean;
    npwp: string | null;
    tax_status: string | null;
    tax_method: string;
    dependents_count: number;
} | null;

export type EmployeeAllowanceRow = {
    id: number;
    component_id: number | null;
    component_name?: string;
    name: string;
    amount: string;
    taxable: boolean;
    effective_start: string | null;
    effective_end: string | null;
    status: string;
    recurring: boolean;
};

export type EmployeeDeductionRow = {
    id: number;
    component_id: number | null;
    component_name?: string;
    name: string;
    value: string;
    effective_start: string | null;
    effective_end: string | null;
    status: string;
    recurring: boolean;
};

export type EmployeeDetail = {
    id: number;
    company_id: number;
    user_id: number | null;
    employee_code: string;
    full_name: string;
    email: string | null;
    phone: string | null;
    gender: string | null;
    birth_date: string | null;
    marital_status: string | null;
    religion: string | null;
    status: string;
    join_date: string | null;
    resign_date: string | null;
    profile_photo_url: string | null;
    company_name: string | null;
    site_name: string | null;
    department_name: string | null;
    position_name: string | null;
    manager_name: string | null;
    identity: EmployeeIdentity;
    tax_profile: EmployeeTaxProfile;
    family_members: Array<{
        id: number;
        name: string;
        relationship: string;
        birth_date: string | null;
        is_dependent: boolean;
    }>;
    emergency_contacts: Array<{
        id: number;
        name: string;
        relationship: string;
        phone: string;
    }>;
    bank_accounts: Array<{
        id: number;
        bank_name: string;
        account_number: string;
        account_holder: string;
        is_primary: boolean;
    }>;
    allowances: EmployeeAllowanceRow[];
    deductions: EmployeeDeductionRow[];
    documents: Array<{
        id: number;
        category: string;
        original_name: string;
        url: string;
        created_at: string;
    }>;
    contracts: Array<{
        id: number;
        contract_type: string;
        start_date: string | null;
        end_date: string | null;
        salary_base: string;
    }>;
    loans?: Array<{
        id: number;
        amount: string;
        remaining_amount: string;
        monthly_deduction: string;
    }>;
    jobs?: Array<{
        id: number;
        company_id: number;
        company_name: string | null;
        department_id: number | null;
        department_name: string | null;
        position_id: number | null;
        position_name: string | null;
        manager_id: number | null;
        manager_name: string | null;
        employment_type: string | null;
        start_date: string | null;
        end_date: string | null;
    }>;
    site_assignments?: Array<{
        id: number;
        site_id: number;
        site_name: string | null;
        start_date: string | null;
        end_date: string | null;
    }>;
    recent_payrolls: Array<{
        id: number;
        period_month: number;
        period_year: number;
        net_salary: string;
        status: string;
    }>;
    recent_attendances: Array<{
        id: number;
        clock_in: string | null;
        clock_out: string | null;
        status: string;
    }>;
    user: { id: number; name: string; email: string } | null;
};

export type UserOption = { id: number; name: string; email: string };
export type CompanyOption = { id: number; name: string };
export type NamedOption = { id: number; name: string };
export type ManagerOption = { id: number; full_name: string; employee_code: string };
