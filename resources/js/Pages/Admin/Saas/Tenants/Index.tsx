import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import type { Paginated } from '@/types/pagination';

type TenantRow = {
    id: number;
    name: string;
    status: string;
    subscriptions_count: number;
    companies_count: number;
    employees_count: number;
};

export default function Index({
    tenants,
    filters,
    flash,
}: PageProps<{ tenants: Paginated<TenantRow>; filters: { per_page: string } }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<TenantRow>
            title={t('tenants')}
            pageTitle={t('tenants')}
            addLabel={t('addTenant')}
            items={tenants}
            filters={filters}
            indexUrl={route('admin.saas.tenants.index')}
            flash={flash}
            columns={[
                { key: 'name', label: t('tenantName') },
                {
                    key: 'status',
                    label: t('status'),
                    render: (row) => (
                        <Badge variant="outline">{row.status}</Badge>
                    ),
                },
                { key: 'companies_count', label: t('companies') },
                { key: 'employees_count', label: t('employees') },
                { key: 'subscriptions_count', label: t('subscriptions') },
            ]}
            fields={[
                {
                    name: 'name',
                    label: t('tenantName'),
                    type: 'text',
                    required: true,
                },
                {
                    name: 'status',
                    label: t('status'),
                    type: 'select',
                    required: true,
                    options: [
                        { value: 'active', label: t('active') },
                        { value: 'suspended', label: t('suspended') },
                        { value: 'cancelled', label: t('cancelled') },
                    ],
                },
            ]}
            initialForm={{ name: '', status: 'active' }}
            mapRowToForm={(row) => ({ name: row.name, status: row.status })}
            storeUrl={route('admin.saas.tenants.store')}
            updateUrl={(id) => route('admin.saas.tenants.update', id)}
            destroyUrl={(id) => route('admin.saas.tenants.destroy', id)}
        />
    );
}
