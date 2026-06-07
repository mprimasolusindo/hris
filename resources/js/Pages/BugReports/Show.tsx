import HrisLayout from '@/Layouts/HrisLayout';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps, type BugReportDetail } from '@/types';
import { Head, Link, router } from '@inertiajs/react';
import { ArrowLeft, Trash2 } from 'lucide-react';
import { useEffect, useState } from 'react';
import { toast } from 'sonner';

export default function Show({
    report,
    statuses,
    flash,
}: PageProps<{
    report: BugReportDetail;
    statuses: string[];
}>) {
    const { t } = useLanguage();
    const [status, setStatus] = useState(report.status);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const statusLabel = (value: string) => {
        const map: Record<string, string> = {
            todo: t('statusTodo'),
            in_progress: t('statusInProgress'),
            failed: t('statusFailed'),
            ready_for_review: t('statusReadyForReview'),
            on_review: t('statusOnReview'),
            closed: t('statusClosed'),
            done: t('statusDone'),
        };
        return map[value] ?? value;
    };

    const updateStatus = (nextStatus: string) => {
        setStatus(nextStatus);
        router.patch(route('bug-reports.status.update', report.id), { status: nextStatus });
    };

    const destroy = () => {
        if (!window.confirm(t('delete') + '?')) return;
        router.delete(route('bug-reports.destroy', report.id));
    };

    return (
        <HrisLayout>
            <Head title={report.title} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <div className="flex items-center gap-3">
                        <Button variant="ghost" size="icon" asChild>
                            <Link href={route('bug-reports.index')}>
                                <ArrowLeft className="h-4 w-4" />
                            </Link>
                        </Button>
                        <div>
                            <h1 className="text-2xl font-bold text-foreground">{report.title}</h1>
                            <p className="text-sm text-muted-foreground">{report.created_at}</p>
                        </div>
                    </div>
                    <Button variant="destructive" onClick={destroy}>
                        <Trash2 className="mr-2 h-4 w-4" />
                        {t('delete')}
                    </Button>
                </div>

                <div className="grid gap-6 lg:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('bugReportDescription')}</CardTitle>
                        </CardHeader>
                        <CardContent className="space-y-4">
                            <p className="whitespace-pre-wrap text-sm">
                                {report.description || '—'}
                            </p>
                            <div className="space-y-2">
                                <Label>{t('status')}</Label>
                                <Select value={status} onValueChange={updateStatus}>
                                    <SelectTrigger>
                                        <SelectValue />
                                    </SelectTrigger>
                                    <SelectContent>
                                        {statuses.map((value) => (
                                            <SelectItem key={value} value={value}>
                                                {statusLabel(value)}
                                            </SelectItem>
                                        ))}
                                    </SelectContent>
                                </Select>
                            </div>
                        </CardContent>
                    </Card>

                    <Card>
                        <CardHeader>
                            <CardTitle>{t('screenshot')}</CardTitle>
                        </CardHeader>
                        <CardContent>
                            {report.screenshot_url ? (
                                <img
                                    src={report.screenshot_url}
                                    alt={report.title}
                                    className="max-h-[480px] w-full rounded-md border object-contain"
                                />
                            ) : (
                                <p className="text-sm text-muted-foreground">—</p>
                            )}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('pageUrl')}</CardTitle>
                    </CardHeader>
                    <CardContent className="space-y-3 text-sm">
                        <div>
                            <span className="font-medium">{t('pageUrl')}: </span>
                            <a
                                href={report.url}
                                className="text-primary underline break-all"
                                target="_blank"
                                rel="noreferrer"
                            >
                                {report.url}
                            </a>
                        </div>
                        <div>
                            <span className="font-medium">{t('pageTitle')}: </span>
                            {report.page_title ?? '—'}
                        </div>
                        <div>
                            <span className="font-medium">{t('viewport')}: </span>
                            {report.viewport_width && report.viewport_height
                                ? `${report.viewport_width} × ${report.viewport_height}`
                                : '—'}
                        </div>
                        <div>
                            <span className="font-medium">{t('userAgent')}: </span>
                            <span className="break-all">{report.user_agent ?? '—'}</span>
                        </div>
                        <div>
                            <span className="font-medium">{t('reportedBy')}: </span>
                            {report.reported_by_name ?? '—'}
                        </div>
                        <div>
                            <span className="font-medium">{t('status')}: </span>
                            <Badge variant="outline">{statusLabel(report.status)}</Badge>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('consoleLog')}</CardTitle>
                    </CardHeader>
                    <CardContent>
                        {report.console_log.length === 0 ? (
                            <p className="text-sm text-muted-foreground">—</p>
                        ) : (
                            <pre className="max-h-64 overflow-auto rounded-md bg-muted p-3 text-xs">
                                {report.console_log.map((entry, index) => (
                                    <div key={index} className="mb-1">
                                        [{entry.level}] {entry.timestamp}: {entry.message}
                                    </div>
                                ))}
                            </pre>
                        )}
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
