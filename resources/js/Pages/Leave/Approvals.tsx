import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import type { Paginated } from '@/types/pagination';
import { PerPageSelect } from '@/Components/table/PerPageSelect';
import { TablePagination } from '@/Components/table/TablePagination';
import { useEffect } from 'react';
import { Check, X } from 'lucide-react';
import { toast } from 'sonner';

type PendingRow = {
    id: number;
    employee_name: string | null;
    employee_code: string | null;
    type: string;
    start_date: string;
    end_date: string;
    reason: string | null;
    created_at: string;
};

export default function Approvals({
    pending,
    filters,
    flash,
}: PageProps<{ pending: Paginated<PendingRow>; filters: { per_page: string } }>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const decide = (id: number, decision: 'approved' | 'rejected') => {
        router.patch(route('leave.approvals.decide', id), { decision });
    };

    return (
        <HrisLayout>
            <Head title={t('leaveApprovals')} />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-foreground">{t('leaveApprovals')}</h1>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('leaveType')}</TableHead>
                                    <TableHead>{t('startDate')}</TableHead>
                                    <TableHead>{t('endDate')}</TableHead>
                                    <TableHead>{t('reason')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {pending.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    pending.data.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.employee_name}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {row.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell>{row.type}</TableCell>
                                            <TableCell>{row.start_date}</TableCell>
                                            <TableCell>{row.end_date}</TableCell>
                                            <TableCell className="max-w-[14rem] text-sm text-muted-foreground">
                                                {row.reason || '—'}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-1">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() =>
                                                            decide(row.id, 'approved')
                                                        }
                                                    >
                                                        <Check className="h-4 w-4 text-emerald-600" />
                                                    </Button>
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        type="button"
                                                        onClick={() =>
                                                            decide(row.id, 'rejected')
                                                        }
                                                    >
                                                        <X className="h-4 w-4 text-destructive" />
                                                    </Button>
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
                <div className="flex flex-wrap items-center justify-between gap-4">
                    <PerPageSelect
                        value={filters.per_page}
                        onChange={(v) =>
                            router.get(route('leave.approvals.index'), { per_page: v, page: 1 })
                        }
                    />
                    <TablePagination paginator={pending} />
                </div>
            </div>
        </HrisLayout>
    );
}
