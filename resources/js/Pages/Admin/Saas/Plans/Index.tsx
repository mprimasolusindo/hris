import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type PlanRow = {
    id: number;
    name: string;
    price: number;
    employee_limit: number | null;
    subscriptions_count: number;
};

function formatIdr(value: number) {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        maximumFractionDigits: 0,
    }).format(value);
}

export default function Index({
    plans,
    flash,
}: PageProps<{ plans: PlanRow[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<PlanRow>
            title={t('plans')}
            pageTitle={t('plans')}
            addLabel={t('addPlan')}
            items={plans}
            flash={flash}
            columns={[
                { key: 'name', label: t('planName') },
                {
                    key: 'price',
                    label: t('price'),
                    render: (row) => formatIdr(row.price),
                },
                {
                    key: 'employee_limit',
                    label: t('employeeLimit'),
                    render: (row) =>
                        row.employee_limit ?? t('unlimited'),
                },
                { key: 'subscriptions_count', label: t('subscriptions') },
            ]}
            fields={[
                {
                    name: 'name',
                    label: t('planName'),
                    type: 'text',
                    required: true,
                },
                {
                    name: 'price',
                    label: t('price'),
                    type: 'text',
                    required: true,
                },
                {
                    name: 'employee_limit',
                    label: t('employeeLimit'),
                    type: 'text',
                },
            ]}
            initialForm={{ name: '', price: '0', employee_limit: '' }}
            mapRowToForm={(row) => ({
                name: row.name,
                price: String(row.price),
                employee_limit:
                    row.employee_limit === null ? '' : String(row.employee_limit),
            })}
            storeUrl={route('admin.saas.plans.store')}
            updateUrl={(id) => route('admin.saas.plans.update', id)}
            destroyUrl={(id) => route('admin.saas.plans.destroy', id)}
        />
    );
}
