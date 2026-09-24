import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import type { Paginated } from '@/types/pagination';

type PositionRow = { id: number; name: string };

export default function Index({
    positions,
    filters,
    flash,
}: PageProps<{ positions: Paginated<PositionRow>; filters: { per_page: string } }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<PositionRow>
            title={t('positions')}
            pageTitle={t('positions')}
            addLabel={t('addPosition')}
            items={positions}
            filters={filters}
            indexUrl={route('organization.positions.index')}
            flash={flash}
            columns={[{ key: 'name', label: t('name') }]}
            fields={[{ name: 'name', label: t('name'), type: 'text', required: true }]}
            initialForm={{ name: '' }}
            mapRowToForm={(row) => ({ name: row.name })}
            storeUrl={route('organization.positions.store')}
            updateUrl={(id) => route('organization.positions.update', id)}
            destroyUrl={(id) => route('organization.positions.destroy', id)}
        />
    );
}
