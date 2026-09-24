import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import type { Paginated } from '@/types/pagination';
import { router } from '@inertiajs/react';
import { Button } from '@/Components/ui/button';
import { Badge } from '@/Components/ui/badge';

type HolidayRow = {
    id: number;
    name: string;
    holiday_date: string;
    type: string;
    is_half_day: boolean;
    notes: string | null;
};

type TypeOption = { value: string; label: string };

export default function Index({
    holidays,
    year,
    typeOptions,
    filters,
    flash,
}: PageProps<{
    holidays: Paginated<HolidayRow>;
    year: number;
    typeOptions: TypeOption[];
    filters: { per_page: string; year: number };
}>) {
    const { t } = useLanguage();

    const typeLabel = (value: string) => {
        if (value === 'national') return t('holidayTypeNational');
        if (value === 'company') return t('holidayTypeCompany');
        if (value === 'joint_leave') return t('holidayTypeJointLeave');
        return value;
    };

    return (
        <MasterCrudPage<HolidayRow>
            title={t('holidays')}
            pageTitle={t('holidays')}
            addLabel={t('addHoliday')}
            items={holidays}
            filters={filters}
            indexUrl={route('holidays.index')}
            flash={flash}
            toolbar={
                <div className="flex items-center gap-2">
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() =>
                            router.get(
                                route('holidays.index'),
                                { year: year - 1, per_page: filters.per_page },
                                { preserveState: true },
                            )
                        }
                    >
                        {year - 1}
                    </Button>
                    <span className="text-sm font-medium tabular-nums">{year}</span>
                    <Button
                        variant="outline"
                        size="sm"
                        onClick={() =>
                            router.get(
                                route('holidays.index'),
                                { year: year + 1, per_page: filters.per_page },
                                { preserveState: true },
                            )
                        }
                    >
                        {year + 1}
                    </Button>
                </div>
            }
            columns={[
                { key: 'holiday_date', label: t('date') },
                { key: 'name', label: t('name') },
                {
                    key: 'type',
                    label: t('type'),
                    render: (row) => typeLabel(row.type),
                },
                {
                    key: 'is_half_day',
                    label: t('halfDay'),
                    render: (row) =>
                        row.is_half_day ? (
                            <Badge variant="secondary">{t('yes')}</Badge>
                        ) : (
                            <span className="text-muted-foreground">—</span>
                        ),
                },
            ]}
            fields={[
                { name: 'name', label: t('name'), type: 'text', required: true },
                { name: 'holiday_date', label: t('date'), type: 'date', required: true },
                {
                    name: 'type',
                    label: t('type'),
                    type: 'select',
                    required: true,
                    options: typeOptions.map((o) => ({
                        value: o.value,
                        label: typeLabel(o.value),
                    })),
                },
                { name: 'is_half_day', label: t('halfDay'), type: 'checkbox' },
                { name: 'notes', label: t('notes'), type: 'textarea' },
            ]}
            initialForm={{
                name: '',
                holiday_date: '',
                type: 'national',
                is_half_day: false,
                notes: '',
            }}
            mapRowToForm={(row) => ({
                name: row.name,
                holiday_date: row.holiday_date,
                type: row.type,
                is_half_day: row.is_half_day,
                notes: row.notes ?? '',
            })}
            storeUrl={route('holidays.store')}
            updateUrl={(id) => route('holidays.update', id)}
            destroyUrl={(id) => route('holidays.destroy', id)}
        />
    );
}
