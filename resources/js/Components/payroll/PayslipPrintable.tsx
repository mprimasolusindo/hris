import { useLanguage } from '@/i18n/LanguageContext';

type Item = { id?: number; name: string; amount: string | number; type: string };

type PayslipPayroll = {
    id: number;
    employee_name: string;
    employee_code: string;
    company_name?: string | null;
    site_name?: string | null;
    period_label: string;
    gross_salary: string | number;
    total_deduction: string | number;
    net_salary: string | number;
    approval_notes?: string | null;
};

interface Props {
    payroll: PayslipPayroll;
    items: Item[];
}

const fmt = (v: string | number) =>
    new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(Number(v || 0));

export function PayslipPrintable({ payroll, items }: Props) {
    const { t } = useLanguage();

    const earnings = items.filter((i) => i.type === 'earning');
    const deductions = items.filter((i) => i.type === 'deduction');

    const variableNames = (n: string) =>
        /^Overtime|^Bonus|^Attendance Incentive|^Leave Adjustment/i.test(n);
    const basic = earnings.filter((i) => i.name === 'Basic Salary');
    const variables = earnings.filter((i) => variableNames(i.name));
    const allowances = earnings.filter(
        (i) => i.name !== 'Basic Salary' && !variableNames(i.name),
    );

    const isBpjs = (n: string) => /bpjs/i.test(n);
    const isTax = (n: string) => /pph|tax|pajak/i.test(n);
    const statutory = deductions.filter((i) => isBpjs(i.name));
    const tax = deductions.filter((i) => isTax(i.name));
    const otherDed = deductions.filter((i) => !isBpjs(i.name) && !isTax(i.name));

    const Section = ({ label, rows }: { label: string; rows: Item[] }) =>
        rows.length === 0 ? null : (
            <>
                <tr>
                    <td
                        colSpan={2}
                        style={{
                            background: '#f5f5f5',
                            padding: '6px 8px',
                            fontSize: 10,
                            fontWeight: 600,
                            textTransform: 'uppercase',
                        }}
                    >
                        {label}
                    </td>
                </tr>
                {rows.map((r, i) => (
                    <tr key={`${label}-${i}`}>
                        <td style={{ padding: '4px 8px 4px 20px', fontSize: 12 }}>
                            {r.name}
                        </td>
                        <td
                            style={{
                                padding: '4px 8px',
                                fontSize: 12,
                                textAlign: 'right',
                            }}
                        >
                            {fmt(r.amount)}
                        </td>
                    </tr>
                ))}
            </>
        );

    return (
        <div
            className="payslip-printable"
            style={{
                display: 'none',
                padding: 32,
                color: '#111',
                fontFamily: 'system-ui, sans-serif',
            }}
        >
            <div
                style={{
                    display: 'flex',
                    justifyContent: 'space-between',
                    borderBottom: '2px solid #111',
                    paddingBottom: 12,
                    marginBottom: 16,
                }}
            >
                <div>
                    <div style={{ fontSize: 18, fontWeight: 700 }}>
                        {payroll.company_name || 'Company'}
                    </div>
                    <div style={{ fontSize: 11, color: '#666' }}>
                        {payroll.site_name || ''}
                    </div>
                </div>
                <div style={{ textAlign: 'right' }}>
                    <div style={{ fontSize: 16, fontWeight: 700 }}>{t('payslipFor')}</div>
                    <div style={{ fontSize: 11, color: '#666' }}>#{payroll.id}</div>
                </div>
            </div>

            <table style={{ width: '100%', marginBottom: 16, fontSize: 12 }}>
                <tbody>
                    <tr>
                        <td style={{ color: '#666' }}>{t('name')}</td>
                        <td style={{ fontWeight: 600 }}>{payroll.employee_name}</td>
                        <td style={{ color: '#666' }}>{t('period')}</td>
                        <td style={{ fontWeight: 600 }}>{payroll.period_label}</td>
                    </tr>
                    <tr>
                        <td style={{ color: '#666' }}>{t('employeeCode')}</td>
                        <td>{payroll.employee_code}</td>
                        <td />
                        <td />
                    </tr>
                </tbody>
            </table>

            <div
                style={{
                    display: 'grid',
                    gridTemplateColumns: '1fr 1fr',
                    gap: 16,
                    marginBottom: 16,
                }}
            >
                <table style={{ width: '100%', borderCollapse: 'collapse', border: '1px solid #ddd' }}>
                    <thead>
                        <tr>
                            <th
                                colSpan={2}
                                style={{
                                    background: '#111',
                                    color: '#fff',
                                    padding: '6px 8px',
                                    textAlign: 'left',
                                    fontSize: 12,
                                }}
                            >
                                {t('earningsBreakdown')}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <Section label={t('basicSalary')} rows={basic} />
                        <Section label={t('allowances')} rows={allowances} />
                        <Section label={t('variables')} rows={variables} />
                        <tr>
                            <td style={{ padding: 8, fontWeight: 700, borderTop: '2px solid #111' }}>
                                {t('totalEarnings')}
                            </td>
                            <td
                                style={{
                                    padding: 8,
                                    fontWeight: 700,
                                    borderTop: '2px solid #111',
                                    textAlign: 'right',
                                }}
                            >
                                {fmt(payroll.gross_salary)}
                            </td>
                        </tr>
                    </tbody>
                </table>

                <table style={{ width: '100%', borderCollapse: 'collapse', border: '1px solid #ddd' }}>
                    <thead>
                        <tr>
                            <th
                                colSpan={2}
                                style={{
                                    background: '#111',
                                    color: '#fff',
                                    padding: '6px 8px',
                                    textAlign: 'left',
                                    fontSize: 12,
                                }}
                            >
                                {t('deductionsBreakdown')}
                            </th>
                        </tr>
                    </thead>
                    <tbody>
                        <Section label="BPJS" rows={statutory} />
                        <Section label="Other" rows={otherDed} />
                        <Section label="PPh21" rows={tax} />
                        <tr>
                            <td style={{ padding: 8, fontWeight: 700, borderTop: '2px solid #111' }}>
                                {t('totalDeduction')}
                            </td>
                            <td
                                style={{
                                    padding: 8,
                                    fontWeight: 700,
                                    borderTop: '2px solid #111',
                                    textAlign: 'right',
                                }}
                            >
                                {fmt(payroll.total_deduction)}
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>

            <div
                style={{
                    background: '#111',
                    color: '#fff',
                    padding: 16,
                    display: 'flex',
                    justifyContent: 'space-between',
                }}
            >
                <div>{t('netSalary')}</div>
                <div style={{ fontSize: 22, fontWeight: 700 }}>{fmt(payroll.net_salary)}</div>
            </div>

            {payroll.approval_notes && (
                <div style={{ marginTop: 16, padding: 12, border: '1px solid #ddd', fontSize: 11 }}>
                    <div style={{ color: '#666' }}>{t('approvalNotes')}</div>
                    <div>{payroll.approval_notes}</div>
                </div>
            )}
        </div>
    );
}
