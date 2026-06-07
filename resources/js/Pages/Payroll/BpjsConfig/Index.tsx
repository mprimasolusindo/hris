import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type BpjsRow = {
    id: number;
    type: string;
    employee_percentage: string;
    company_percentage: string;
};

const percent = (value: string) => `${(Number(value) * 100).toFixed(2)}%`;

const TYPE_OPTIONS = [
    { value: 'kesehatan', label: 'BPJS Kesehatan' },
    { value: 'jht', label: 'JHT — Jaminan Hari Tua' },
    { value: 'jp', label: 'JP — Jaminan Pensiun' },
    { value: 'jkk', label: 'JKK — Jaminan Kecelakaan Kerja' },
    { value: 'jkm', label: 'JKM — Jaminan Kematian' },
    { value: 'jkp', label: 'JKP — Jaminan Kehilangan Pekerjaan' },
];

export default function Index({
    items,
    flash,
}: PageProps<{ items: BpjsRow[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<BpjsRow>
            title={t('bpjsConfig')}
            pageTitle={t('bpjsConfig')}
            addLabel={t('addBpjsConfig')}
            items={items}
            flash={flash}
            columns={[
                {
                    key: 'type',
                    label: t('bpjsType'),
                    render: (row) => (
                        <Badge variant="secondary">{row.type.toUpperCase()}</Badge>
                    ),
                },
                {
                    key: 'employee_percentage',
                    label: t('employeeShare'),
                    render: (row) => percent(row.employee_percentage),
                },
                {
                    key: 'company_percentage',
                    label: t('companyShare'),
                    render: (row) => percent(row.company_percentage),
                },
            ]}
            fields={[
                {
                    name: 'type',
                    label: t('bpjsType'),
                    type: 'select',
                    required: true,
                    options: TYPE_OPTIONS,
                },
                {
                    name: 'employee_percentage',
                    label: t('employeeShare'),
                    type: 'text',
                    required: true,
                },
                {
                    name: 'company_percentage',
                    label: t('companyShare'),
                    type: 'text',
                    required: true,
                },
            ]}
            initialForm={{
                type: 'kesehatan',
                employee_percentage: '0',
                company_percentage: '0',
            }}
            mapRowToForm={(row) => ({
                type: row.type,
                employee_percentage: String(row.employee_percentage),
                company_percentage: String(row.company_percentage),
            })}
            storeUrl={route('payroll.bpjs-config.store')}
            updateUrl={(id) => route('payroll.bpjs-config.update', id)}
            destroyUrl={(id) => route('payroll.bpjs-config.destroy', id)}
        />
    );
}
