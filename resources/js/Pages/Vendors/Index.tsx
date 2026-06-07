import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type VendorRow = { id: number; name: string; placement_count: number };

export default function Index({
    vendors,
    flash,
}: PageProps<{ vendors: VendorRow[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<VendorRow>
            title={t('vendors')}
            pageTitle={t('vendors')}
            addLabel={t('addVendor')}
            items={vendors}
            flash={flash}
            detailUrl={(id) => route('vendors.show', id)}
            columns={[
                { key: 'name', label: t('vendorName') },
                {
                    key: 'placement_count',
                    label: t('activePlacements'),
                },
            ]}
            fields={[{ name: 'name', label: t('vendorName'), type: 'text', required: true }]}
            initialForm={{ name: '' }}
            mapRowToForm={(row) => ({ name: row.name })}
            storeUrl={route('vendors.store')}
            updateUrl={(id) => route('vendors.update', id)}
            destroyUrl={(id) => route('vendors.destroy', id)}
        />
    );
}
