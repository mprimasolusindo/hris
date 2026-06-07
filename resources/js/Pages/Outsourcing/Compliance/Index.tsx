import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
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
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import { useEffect } from 'react';
import { CheckCircle2 } from 'lucide-react';
import { toast } from 'sonner';

type FlagRow = {
    id: string;
    severity: string;
    type: string;
    employee_id: number;
    vendor_id: number;
    vendor_name: string | null;
    employee_name: string | null;
    employee_code: string | null;
    detail: string;
};

type ResolvedRow = {
    id: number;
    type: string;
    vendor_name: string | null;
    employee_name: string | null;
    employee_code: string | null;
    detail: string;
    resolved_by: string | null;
    resolved_at: string | null;
};

const severityClass: Record<string, string> = {
    high: 'bg-destructive/10 text-destructive border-destructive/30',
    medium: 'bg-amber-500/10 text-amber-700 border-amber-500/30',
    low: 'bg-muted text-muted-foreground border-border',
};

export default function Index({
    flags,
    resolved,
    filters,
    vendors,
    summary,
    flash,
}: PageProps<{
    flags: FlagRow[];
    resolved: ResolvedRow[];
    filters: { vendor_id: string; severity: string };
    vendors: Array<{ id: number; name: string }>;
    summary: {
        total: number;
        high: number;
        medium: number;
        low: number;
        resolved: number;
    };
}>) {
    const { t } = useLanguage();

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const apply = (patch: Partial<typeof filters>) => {
        router.get(route('outsourcing.compliance.index'), {
            ...filters,
            ...patch,
        });
    };

    const resolveFlag = (row: FlagRow) => {
        router.post(route('outsourcing.compliance.resolve'), {
            employee_id: row.employee_id,
            vendor_id: row.vendor_id,
            flag_type: row.type,
            description: row.detail,
        });
    };

    return (
        <HrisLayout>
            <Head title={t('compliance')} />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-foreground">
                    {t('compliance')}
                </h1>

                <div className="grid gap-4 sm:grid-cols-5">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('openFlags')}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold">
                            {summary.total}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('high')}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold text-destructive">
                            {summary.high}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('medium')}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold">
                            {summary.medium}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('low')}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold">
                            {summary.low}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('resolved')}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold text-green-600">
                            {summary.resolved}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap gap-4 p-4">
                        <div className="space-y-2">
                            <Label>{t('vendors')}</Label>
                            <Select
                                value={filters.vendor_id || 'all'}
                                onValueChange={(v) =>
                                    apply({ vendor_id: v === 'all' ? '' : v })
                                }
                            >
                                <SelectTrigger className="w-48">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        {t('all')}
                                    </SelectItem>
                                    {vendors.map((v) => (
                                        <SelectItem
                                            key={v.id}
                                            value={String(v.id)}
                                        >
                                            {v.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('severity')}</Label>
                            <Select
                                value={filters.severity || 'all'}
                                onValueChange={(v) =>
                                    apply({ severity: v === 'all' ? '' : v })
                                }
                            >
                                <SelectTrigger className="w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">
                                        {t('all')}
                                    </SelectItem>
                                    <SelectItem value="high">
                                        {t('high')}
                                    </SelectItem>
                                    <SelectItem value="medium">
                                        {t('medium')}
                                    </SelectItem>
                                    <SelectItem value="low">
                                        {t('low')}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('openFlags')}</CardTitle>
                    </CardHeader>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('severity')}</TableHead>
                                    <TableHead>{t('flagType')}</TableHead>
                                    <TableHead>{t('vendors')}</TableHead>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('detail')}</TableHead>
                                    <TableHead className="text-right">
                                        {t('actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {flags.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={6}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    flags.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                <Badge
                                                    variant="outline"
                                                    className={
                                                        severityClass[
                                                            row.severity
                                                        ]
                                                    }
                                                >
                                                    {row.severity}
                                                </Badge>
                                            </TableCell>
                                            <TableCell className="font-mono text-xs">
                                                {row.type}
                                            </TableCell>
                                            <TableCell>
                                                {row.vendor_name}
                                            </TableCell>
                                            <TableCell>
                                                {row.employee_name}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {row.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell className="text-sm text-muted-foreground">
                                                {row.detail}
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <Button
                                                    variant="outline"
                                                    size="sm"
                                                    onClick={() =>
                                                        resolveFlag(row)
                                                    }
                                                >
                                                    <CheckCircle2 className="mr-1 h-4 w-4" />
                                                    {t('resolve')}
                                                </Button>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>

                {resolved.length > 0 && (
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('resolved')}</CardTitle>
                        </CardHeader>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('flagType')}</TableHead>
                                        <TableHead>{t('vendors')}</TableHead>
                                        <TableHead>{t('employee')}</TableHead>
                                        <TableHead>{t('resolvedBy')}</TableHead>
                                        <TableHead>{t('resolvedAt')}</TableHead>
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {resolved.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell className="font-mono text-xs">
                                                {row.type}
                                            </TableCell>
                                            <TableCell>
                                                {row.vendor_name}
                                            </TableCell>
                                            <TableCell>
                                                {row.employee_name}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {row.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell>
                                                {row.resolved_by ?? '-'}
                                            </TableCell>
                                            <TableCell className="text-xs text-muted-foreground">
                                                {row.resolved_at ?? '-'}
                                            </TableCell>
                                        </TableRow>
                                    ))}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}
            </div>
        </HrisLayout>
    );
}
