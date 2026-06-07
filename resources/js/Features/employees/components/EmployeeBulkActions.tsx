import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { router } from '@inertiajs/react';
import { Download, Upload } from 'lucide-react';
import { useRef } from 'react';

type Props = {
    selectedIds: number[];
    onClear: () => void;
};

export function EmployeeBulkActions({ selectedIds, onClear }: Props) {
    const { t } = useLanguage();
    const importRef = useRef<HTMLInputElement>(null);

    const bulkArchive = () => {
        if (!window.confirm(`Archive ${selectedIds.length} employee(s)?`)) return;
        router.post(
            route('employees.bulk'),
            { ids: selectedIds, action: 'archive' },
            { preserveScroll: true, onSuccess: onClear },
        );
    };

    const exportCsv = () => {
        const q =
            selectedIds.length > 0
                ? `?ids=${selectedIds.join(',')}`
                : '';
        window.location.href = route('employees.export') + q;
    };

    const onImportFile = (file: File) => {
        router.post(
            route('employees.import'),
            { file },
            { forceFormData: true, preserveScroll: true },
        );
    };

    if (selectedIds.length === 0) {
        return (
            <div className="flex flex-wrap gap-2">
                <Button variant="outline" size="sm" onClick={exportCsv}>
                    <Download className="mr-2 h-4 w-4" />
                    Export CSV
                </Button>
                <input
                    ref={importRef}
                    type="file"
                    accept=".csv,text/csv"
                    className="hidden"
                    onChange={(e) => {
                        const f = e.target.files?.[0];
                        if (f) onImportFile(f);
                        e.target.value = '';
                    }}
                />
                <Button
                    variant="outline"
                    size="sm"
                    onClick={() => importRef.current?.click()}
                >
                    <Upload className="mr-2 h-4 w-4" />
                    Import CSV
                </Button>
            </div>
        );
    }

    return (
        <div className="flex flex-wrap items-center gap-2 rounded-md border bg-muted/50 p-3">
            <span className="text-sm font-medium">
                {selectedIds.length} selected
            </span>
            <Button variant="destructive" size="sm" onClick={bulkArchive}>
                Archive
            </Button>
            <Button variant="outline" size="sm" onClick={exportCsv}>
                <Download className="mr-2 h-4 w-4" />
                Export selected
            </Button>
            <Button variant="ghost" size="sm" onClick={onClear}>
                {t('cancel')}
            </Button>
        </div>
    );
}
