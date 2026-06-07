import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Badge } from '@/Components/ui/badge';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';

type ComponentRow = { id: number; name: string; type: string; is_taxable: boolean };

export default function Index({
    items,
    flash,
}: PageProps<{ items: ComponentRow[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<ComponentRow>
            title={t('masterDeductions')}
            pageTitle={t('masterDeductions')}
            addLabel="Add deduction"
            items={items}
            flash={flash}
            columns={[
                { key: 'name', label: t('name') },
                {
                    key: 'is_taxable',
                    label: 'Taxable',
                    render: (row) => (
                        <Badge variant={row.is_taxable ? 'default' : 'secondary'}>
                            {row.is_taxable ? 'Yes' : 'No'}
                        </Badge>
                    ),
                },
            ]}
            fields={[
                { name: 'name', label: t('name'), type: 'text', required: true },
                { name: 'is_taxable', label: 'Taxable', type: 'checkbox' },
            ]}
            initialForm={{ name: '', is_taxable: false }}
            mapRowToForm={(row) => ({
                name: row.name,
                is_taxable: row.is_taxable,
            })}
            storeUrl={route('payroll.master-deductions.store')}
            updateUrl={(id) => route('payroll.master-deductions.update', id)}
            destroyUrl={(id) => route('payroll.master-deductions.destroy', id)}
        />
    );
}
