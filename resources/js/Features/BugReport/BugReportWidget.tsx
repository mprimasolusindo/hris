import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';
import { Bug } from 'lucide-react';
import { useState } from 'react';
import BugReportModal from './BugReportModal';

export default function BugReportWidget() {
    const { t } = useLanguage();
    const { bugReport } = usePage<PageProps>().props;
    const [open, setOpen] = useState(false);

    if (!bugReport?.enabled) {
        return null;
    }

    return (
        <>
            <button
                type="button"
                data-bug-report-ui
                onClick={() => setOpen(true)}
                className="fixed right-0 top-1/2 z-50 flex -translate-y-1/2 flex-col items-center gap-1 rounded-l-lg bg-red-600 px-2 py-4 text-white shadow-lg transition hover:bg-red-700"
                aria-label={t('report')}
            >
                <Bug className="h-5 w-5" />
                <span
                    className="text-xs font-semibold tracking-widest [writing-mode:vertical-rl] rotate-180"
                >
                    {t('report').toUpperCase()}
                </span>
            </button>

            <BugReportModal open={open} onOpenChange={setOpen} />
        </>
    );
}
