import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Card, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
import { Plus, Search, Eye } from 'lucide-react';
import { useEffect, useState } from 'react';
import { PageProps } from '@/types';
import type { Paginated } from '@/types/pagination';
import { PerPageSelect } from '@/Components/table/PerPageSelect';
import { TablePagination } from '@/Components/table/TablePagination';
import { toast } from 'sonner';
import { Checkbox } from '@/Components/ui/checkbox';
import { EmployeeBulkActions } from '@/Features/employees/components/EmployeeBulkActions';

type EmployeeRow = {
    id: number;
    employee_code: string;
    full_name: string;
    company_name: string | null;
    site_name: string | null;
    department_name: string | null;
    status: string;
    join_date: string | null;
};

export default function Index({
    employees,
    filters,
    flash,
}: PageProps<{
    employees: Paginated<EmployeeRow>;
    filters: { search: string; status: string; per_page: string };
}>) {
    const { t } = useLanguage();
    const [search, setSearch] = useState(filters.search || '');
    const [statusFilter, setStatusFilter] = useState(filters.status || 'all');
    const [selectedIds, setSelectedIds] = useState<number[]>([]);

    const toggleSelect = (id: number, checked: boolean) => {
        setSelectedIds((prev) =>
            checked ? [...prev, id] : prev.filter((x) => x !== id),
        );
    };

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash?.success]);

    const applyFilters = (nextSearch = search, nextStatus = statusFilter) => {
        router.get(
            route('employees.index'),
            {
                search: nextSearch || undefined,
                status: nextStatus === 'all' ? undefined : nextStatus,
                per_page: filters.per_page,
            },
            { preserveState: true, replace: true },
        );
    };

    const statusBadge = (status: string) => {
        const variants: Record<string, 'default' | 'secondary' | 'destructive'> =
            {
                active: 'default',
                inactive: 'secondary',
                resigned: 'secondary',
                terminated: 'destructive',
                retired: 'secondary',
                suspended: 'destructive',
            };
        return (
            <Badge variant={variants[status] || 'default'}>
                {status}
            </Badge>
        );
    };

    return (
        <HrisLayout>
            <Head title={t('employees')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">
                        {t('employees')}
                    </h1>
                    <div className="flex flex-wrap gap-2">
                        <EmployeeBulkActions
                            selectedIds={selectedIds}
                            onClear={() => setSelectedIds([])}
                        />
                        <Button asChild>
                            <Link href={route('employees.create')}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addEmployee')}
                            </Link>
                        </Button>
                    </div>
                </div>

                <Card className="shadow-sm">
                    <CardContent className="p-4">
                        <div className="flex flex-wrap gap-4">
                            <div className="relative max-w-sm flex-1">
                                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                                <Input
                                    placeholder={t('search')}
                                    className="pl-8"
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    onKeyDown={(e) => {
                                        if (e.key === 'Enter') {
                                            applyFilters();
                                        }
                                    }}
                                />
                            </div>
                            <Select
                                value={statusFilter || 'all'}
                                onValueChange={(v) => {
                                    setStatusFilter(v);
                                    applyFilters(search, v);
                                }}
                            >
                                <SelectTrigger className="w-40">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">{t('all')}</SelectItem>
                                    <SelectItem value="active">{t('active')}</SelectItem>
                                    <SelectItem value="resigned">
                                        {t('inactive')}
                                    </SelectItem>
                                    <SelectItem value="terminated">
                                        {t('terminated')}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                            <Button
                                variant="secondary"
                                onClick={() => applyFilters()}
                            >
                                {t('search')}
                            </Button>
                        </div>
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead className="w-10" />
                                    <TableHead>{t('employeeCode')}</TableHead>
                                    <TableHead>{t('name')}</TableHead>
                                    <TableHead>{t('company')}</TableHead>
                                    <TableHead>{t('site')}</TableHead>
                                    <TableHead>{t('department')}</TableHead>
                                    <TableHead>{t('status')}</TableHead>
                                    <TableHead>{t('joinDate')}</TableHead>
                                    <TableHead>{t('actions')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {employees.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={9}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    employees.data.map((employee) => (
                                        <TableRow key={employee.id}>
                                            <TableCell>
                                                <Checkbox
                                                    checked={selectedIds.includes(employee.id)}
                                                    onCheckedChange={(v) =>
                                                        toggleSelect(employee.id, !!v)
                                                    }
                                                />
                                            </TableCell>
                                            <TableCell className="font-medium">
                                                {employee.employee_code}
                                            </TableCell>
                                            <TableCell>
                                                {employee.full_name}
                                            </TableCell>
                                            <TableCell>
                                                {employee.company_name ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                {employee.site_name ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                {employee.department_name ??
                                                    '-'}
                                            </TableCell>
                                            <TableCell>
                                                {statusBadge(employee.status)}
                                            </TableCell>
                                            <TableCell>
                                                {employee.join_date ?? '-'}
                                            </TableCell>
                                            <TableCell>
                                                <div className="flex gap-2">
                                                    <Button
                                                        variant="ghost"
                                                        size="icon"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={route(
                                                                'employees.show',
                                                                employee.id,
                                                            )}
                                                        >
                                                            <Eye className="h-4 w-4" />
                                                        </Link>
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
                            router.get(
                                route('employees.index'),
                                {
                                    search: filters.search || undefined,
                                    status: filters.status || undefined,
                                    per_page: v,
                                    page: 1,
                                },
                                { preserveScroll: true },
                            )
                        }
                    />
                    <TablePagination paginator={employees} />
                </div>
            </div>
        </HrisLayout>
    );
}
