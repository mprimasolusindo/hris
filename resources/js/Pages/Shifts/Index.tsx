import MasterCrudPage from '@/Components/master/MasterCrudPage';
import { Button } from '@/Components/ui/button';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import { Link } from '@inertiajs/react';
import { Calendar, Users } from 'lucide-react';

type ShiftRow = {
    id: number;
    name: string;
    start_time: string;
    end_time: string;
};

export default function Index({
    shifts,
    flash,
}: PageProps<{ shifts: ShiftRow[] }>) {
    const { t } = useLanguage();

    return (
        <MasterCrudPage<ShiftRow>
            title={t('shifts')}
            pageTitle={t('shifts')}
            addLabel={t('addShift')}
            items={shifts}
            flash={flash}
            detailUrl={(id) => route('shifts.show', id)}
            toolbar={
                <>
                    <Button variant="outline" asChild>
                        <Link href={route('shifts.calendar')}>
                            <Calendar className="mr-2 h-4 w-4" />
                            {t('calendarView')}
                        </Link>
                    </Button>
                    <Button variant="outline" asChild>
                        <Link href={route('shifts.assign')}>
                            <Users className="mr-2 h-4 w-4" />
                            {t('bulkAssign')}
                        </Link>
                    </Button>
                </>
            }
            columns={[
                { key: 'name', label: t('name') },
                { key: 'start_time', label: t('startTime') },
                { key: 'end_time', label: t('endTime') },
            ]}
            fields={[
                { name: 'name', label: t('name'), type: 'text', required: true },
                {
                    name: 'start_time',
                    label: t('startTime'),
                    type: 'time',
                    required: true,
                },
                {
                    name: 'end_time',
                    label: t('endTime'),
                    type: 'time',
                    required: true,
                },
            ]}
            initialForm={{ name: '', start_time: '08:00', end_time: '17:00' }}
            mapRowToForm={(row) => ({
                name: row.name,
                start_time: row.start_time,
                end_time: row.end_time,
            })}
            storeUrl={route('shifts.store')}
            updateUrl={(id) => route('shifts.update', id)}
            destroyUrl={(id) => route('shifts.destroy', id)}
        />
    );
}
