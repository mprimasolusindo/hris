import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type DepartmentRow = {
    id: number;
    name: string;
    company_id: number;
    company_name: string | null;
};

type CompanyOption = { id: number; name: string };

export default function Index({
    departments,
    companies,
    flash,
}: PageProps<{ departments: DepartmentRow[]; companies: CompanyOption[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<DepartmentRow>
            title={t('departments')}
            pageTitle={t('departments')}
            addLabel={t('addDepartment')}
            items={departments}
            flash={flash}
            columns={[
                { key: 'name', label: t('name') },
                { key: 'company_name', label: t('company') },
            ]}
            fields={[
                {
                    name: 'company_id',
                    label: t('company'),
                    type: 'select',
                    required: true,
                    options: companies.map((c) => ({
                        value: String(c.id),
                        label: c.name,
                    })),
                },
                { name: 'name', label: t('name'), type: 'text', required: true },
            ]}
            initialForm={{
                company_id: String(companies[0]?.id ?? ''),
                name: '',
            }}
            mapRowToForm={(row) => ({
                company_id: String(row.company_id),
                name: row.name,
            })}
            storeUrl={route('organization.departments.store')}
            updateUrl={(id) => route('organization.departments.update', id)}
            destroyUrl={(id) => route('organization.departments.destroy', id)}
        />
    );
}
