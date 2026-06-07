import HrisLayout from '@/Layouts/HrisLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps, type BugReportRow } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { Bug, Settings } from 'lucide-react';
import { useEffect } from 'react';
import { toast } from 'sonner';

const statusVariant = (status: string) => {
    switch (status) {
        case 'done':
        case 'closed':
            return 'default';
        case 'failed':
            return 'destructive';
        case 'in_progress':
        case 'on_review':
            return 'secondary';
        default:
            return 'outline';
    }
};

export default function Index({
    reports,
    filters,
    statuses,
    flash,
}: PageProps<{
    reports: BugReportRow[];
    filters: { status: string };
    statuses: string[];
}>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const statusLabel = (status: string) => {
        const map: Record<string, string> = {
            todo: t('statusTodo'),
            in_progress: t('statusInProgress'),
            failed: t('statusFailed'),
            ready_for_review: t('statusReadyForReview'),
            on_review: t('statusOnReview'),
            closed: t('statusClosed'),
            done: t('statusDone'),
        };
        return map[status] ?? status;
    };

    const onStatusFilter = (value: string) => {
        router.get(
            route('bug-reports.index'),
            value === 'all' ? {} : { status: value },
            { preserveState: true, replace: true },
        );
    };

    return (
        <HrisLayout>
            <Head title={t('bugReports')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-2">
                        <Bug className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold text-foreground">{t('bugReports')}</h1>
                    </div>
                    <Button variant="outline" asChild>
                        <Link href={route('bug-reports.settings.edit')}>
                            <Settings className="mr-2 h-4 w-4" />
                            {t('bugReportSettings')}
                        </Link>
                    </Button>
                </div>

                <Card>
                    <CardHeader className="flex flex-row items-center justify-between space-y-0">
                        <CardTitle>{t('bugReports')}</CardTitle>
                        <Select
                            value={filters.status || 'all'}
                            onValueChange={onStatusFilter}
                        >
                            <SelectTrigger className="w-[220px]">
                                <SelectValue placeholder={t('filterByStatus')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('allStatuses')}</SelectItem>
                                {statuses.map((status) => (
                                    <SelectItem key={status} value={status}>
                                        {statusLabel(status)}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </CardHeader>
                    <CardContent>
                        {reports.length === 0 ? (
                            <p className="text-sm text-muted-foreground">{t('noBugReports')}</p>
                        ) : (
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('bugReportTitle')}</TableHead>
                                        <TableHead>{t('status')}</TableHead>
                                        <TableHead>{t('pageUrl')}</TableHead>
                                        <TableHead>{t('reportedBy')}</TableHead>
                                        <TableHead>{t('date')}</TableHead>
                                        <TableHead>{t('actions')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {reports.map((report) => (
                                        <TableRow key={report.id}>
                                            <TableCell className="font-medium">{report.title}</TableCell>
                                            <TableCell>
                                                <Badge variant={statusVariant(report.status)}>
                                                    {statusLabel(report.status)}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="max-w-[200px] truncate">
                                                {report.url}
                                            </TableCell>
                                            <TableCell>{report.reported_by_name ?? '—'}</TableCell>
                                            <TableCell>{report.created_at ?? '—'}</TableCell>
                                            <TableCell>
                                                <Button variant="link" className="h-auto p-0" asChild>
                                                    <Link href={route('bug-reports.show', report.id)}>
                                                        {t('view')}
                                                    </Link>
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        )}
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
