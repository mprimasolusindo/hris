import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import type { Paginated } from '@/types/pagination';

type CompanyRow = { id: number; name: string; type: string };

export default function Index({
    companies,
    filters,
    flash,
}: PageProps<{ companies: Paginated<CompanyRow>; filters: { per_page: string } }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<CompanyRow>
            title={t('companies')}
            pageTitle={t('companies')}
            addLabel={t('addCompany')}
            items={companies}
            filters={filters}
            indexUrl={route('organization.companies.index')}
            flash={flash}
            columns={[
                { key: 'name', label: t('name') },
                { key: 'type', label: t('status') },
            ]}
            fields={[
                { name: 'name', label: t('name'), type: 'text', required: true },
                {
                    name: 'type',
                    label: 'Type',
                    type: 'select',
                    required: true,
                    options: [
                        { value: 'main', label: 'Main' },
                        { value: 'vendor', label: 'Vendor' },
                    ],
                },
            ]}
            initialForm={{ name: '', type: 'main' }}
            mapRowToForm={(row) => ({ name: row.name, type: row.type })}
            storeUrl={route('organization.companies.store')}
            updateUrl={(id) => route('organization.companies.update', id)}
            destroyUrl={(id) => route('organization.companies.destroy', id)}
        />
    );
}
