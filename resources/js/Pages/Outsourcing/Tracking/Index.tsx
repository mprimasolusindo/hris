import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
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

type TrackingRow = {
    id: number;
    vendor_name: string | null;
    employee_name: string | null;
    employee_code: string | null;
    present_days: number;
    attendance_records: number;
};

export default function Index({
    rows,
    filters,
    vendors,
    sites,
    summary,
}: PageProps<{
    rows: TrackingRow[];
    filters: { month: number; year: number; vendor_id: string; site_id: string };
    vendors: Array<{ id: number; name: string }>;
    sites: Array<{ id: number; name: string }>;
    summary: { active_placements: number; total_present_days: number };
}>) {
    const { t } = useLanguage();

    const apply = (patch: Partial<typeof filters>) => {
        router.get(route('outsourcing.tracking.index'), { ...filters, ...patch });
    };

    return (
        <HrisLayout>
            <Head title={t('placementTracking')} />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-foreground">{t('placementTracking')}</h1>

                <div className="grid gap-4 sm:grid-cols-2">
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('activePlacements')}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold">
                            {summary.active_placements}
                        </CardContent>
                    </Card>
                    <Card>
                        <CardHeader>
                            <CardTitle>{t('presentCount')}</CardTitle>
                        </CardHeader>
                        <CardContent className="text-2xl font-semibold">
                            {summary.total_present_days}
                        </CardContent>
                    </Card>
                </div>

                <Card>
                    <CardContent className="flex flex-wrap gap-4 p-4">
                        <div className="space-y-2">
                            <Label>{t('month')}</Label>
                            <Input
                                type="number"
                                min={1}
                                max={12}
                                className="w-24"
                                value={filters.month}
                                onChange={(e) => apply({ month: Number(e.target.value) })}
                            />
                        </div>
                        <div className="space-y-2">
                            <Label>{t('year')}</Label>
                            <Input
                                type="number"
                                className="w-28"
                                value={filters.year}
                                onChange={(e) => apply({ year: Number(e.target.value) })}
                            />
                        </div>
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
                                    <SelectItem value="all">{t('all')}</SelectItem>
                                    {vendors.map((v) => (
                                        <SelectItem key={v.id} value={String(v.id)}>
                                            {v.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                        <div className="space-y-2">
                            <Label>{t('site')}</Label>
                            <Select
                                value={filters.site_id || 'all'}
                                onValueChange={(v) =>
                                    apply({ site_id: v === 'all' ? '' : v })
                                }
                            >
                                <SelectTrigger className="w-48">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('all')}</SelectItem>
                                    {sites.map((s) => (
                                        <SelectItem key={s.id} value={String(s.id)}>
                                            {s.name}
                                        </SelectItem>
                                    ))}
                                </SelectContent>
                            </Select>
                        </div>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('employee')}</TableHead>
                                    <TableHead>{t('vendors')}</TableHead>
                                    <TableHead>{t('presentCount')}</TableHead>
                                    <TableHead>{t('attendance')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {rows.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    rows.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                {row.employee_name}
                                                <span className="ml-1 text-xs text-muted-foreground">
                                                    {row.employee_code}
                                                </span>
                                            </TableCell>
                                            <TableCell>{row.vendor_name}</TableCell>
                                            <TableCell>{row.present_days}</TableCell>
                                            <TableCell>{row.attendance_records}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
