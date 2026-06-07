import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type SiteRow = {
    id: number;
    name: string;
    location: string | null;
    company_id: number;
    company_name: string | null;
};

type CompanyOption = { id: number; name: string };

export default function Index({
    sites,
    companies,
    flash,
}: PageProps<{ sites: SiteRow[]; companies: CompanyOption[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<SiteRow>
            title={t('sites')}
            pageTitle={t('sites')}
            addLabel={t('addSite')}
            items={sites}
            flash={flash}
            columns={[
                { key: 'name', label: t('name') },
                { key: 'company_name', label: t('company') },
                { key: 'location', label: t('address') },
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
                { name: 'location', label: t('address'), type: 'text' },
            ]}
            initialForm={{
                company_id: String(companies[0]?.id ?? ''),
                name: '',
                location: '',
            }}
            mapRowToForm={(row) => ({
                company_id: String(row.company_id),
                name: row.name,
                location: row.location ?? '',
            })}
            storeUrl={route('organization.sites.store')}
            updateUrl={(id) => route('organization.sites.update', id)}
            destroyUrl={(id) => route('organization.sites.destroy', id)}
        />
    );
}
