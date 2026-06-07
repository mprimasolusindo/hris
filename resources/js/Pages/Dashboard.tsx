import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import {
    Users,
    UserCheck,
    Clock,
    DollarSign,
    Plane,
    FileSignature,
    CalendarClock,
    ArrowRight,
    Building,
    ShieldCheck,
    UsersRound,
} from 'lucide-react';
import {
    ChartContainer,
    ChartTooltip,
    ChartTooltipContent,
    type ChartConfig,
} from '@/Components/ui/chart';
import {
    LineChart,
    Line,
    XAxis,
    YAxis,
    BarChart,
    Bar,
    CartesianGrid,
} from 'recharts';
import { useReminders } from '@/hooks/useReminders';
import { PageProps } from '@/types';

const attendanceChartConfig: ChartConfig = {
    present: { label: 'Present', color: 'hsl(var(--primary))' },
    late: { label: 'Late', color: 'hsl(var(--destructive))' },
};

const payrollChartConfig: ChartConfig = {
    amount: { label: 'Amount', color: 'hsl(var(--primary))' },
};

type DashboardStats = {
    totalEmployees: number;
    activeEmployees: number;
    attendanceToday: number;
    payrollThisMonth: number;
};

type ChartData = {
    attendance: Array<{ month: string; present: number; late: number }>;
    payroll: Array<{ month: string; amount: number }>;
};

type ActivityItem = {
    type: 'leave' | 'hire' | 'payroll';
    title: string;
    detail: string | null;
    status: string | null;
    timestamp: string | null;
};

const activityIcon: Record<ActivityItem['type'], typeof Plane> = {
    leave: Plane,
    hire: UserCheck,
    payroll: DollarSign,
};

export default function Dashboard({
    stats,
    chartData,
    recentActivity,
}: PageProps<{
    stats: DashboardStats;
    chartData: ChartData;
    recentActivity: ActivityItem[];
}>) {
    const { t } = useLanguage();
    const { data: reminders } = useReminders();

    const attendanceData = chartData.attendance;
    const payrollData = chartData.payroll;

    const formatIdr = (value: number) =>
        new Intl.NumberFormat('id-ID', {
            style: 'currency',
            currency: 'IDR',
            maximumFractionDigits: 0,
        }).format(value);

    const kpiCards = [
        {
            label: t('totalEmployees'),
            value: stats.totalEmployees,
            icon: Users,
            color: 'text-primary',
        },
        {
            label: t('activeEmployees'),
            value: stats.activeEmployees,
            icon: UserCheck,
            color: 'text-green-600',
        },
        {
            label: t('attendanceToday'),
            value: stats.attendanceToday,
            icon: Clock,
            color: 'text-blue-600',
        },
        {
            label: t('payrollThisMonth'),
            value: formatIdr(stats.payrollThisMonth),
            icon: DollarSign,
            color: 'text-amber-600',
        },
    ];

    return (
        <HrisLayout>
            <Head title={t('dashboard')} />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-foreground">
                    {t('dashboard')}
                </h1>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-2 lg:grid-cols-4">
                    {kpiCards.map((card) => (
                        <Card key={card.label} className="shadow-sm">
                            <CardContent className="p-6">
                                <div className="flex items-center justify-between">
                                    <div>
                                        <p className="text-sm text-muted-foreground">
                                            {card.label}
                                        </p>
                                        <p className="mt-1 text-2xl font-bold text-foreground">
                                            {card.value}
                                        </p>
                                    </div>
                                    <card.icon
                                        className={`h-8 w-8 opacity-80 ${card.color}`}
                                    />
                                </div>
                            </CardContent>
                        </Card>
                    ))}
                </div>

                <div className="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">
                                {t('attendanceTrend')}
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ChartContainer
                                config={attendanceChartConfig}
                                className="h-[250px]"
                            >
                                <LineChart data={attendanceData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="month" />
                                    <YAxis />
                                    <ChartTooltip
                                        content={<ChartTooltipContent />}
                                    />
                                    <Line
                                        type="monotone"
                                        dataKey="present"
                                        stroke="var(--color-present)"
                                        strokeWidth={2}
                                    />
                                    <Line
                                        type="monotone"
                                        dataKey="late"
                                        stroke="var(--color-late)"
                                        strokeWidth={2}
                                    />
                                </LineChart>
                            </ChartContainer>
                        </CardContent>
                    </Card>

                    <Card className="shadow-sm">
                        <CardHeader>
                            <CardTitle className="text-base">
                                {t('payrollCost')} (Juta Rp)
                            </CardTitle>
                        </CardHeader>
                        <CardContent>
                            <ChartContainer
                                config={payrollChartConfig}
                                className="h-[250px]"
                            >
                                <BarChart data={payrollData}>
                                    <CartesianGrid strokeDasharray="3 3" />
                                    <XAxis dataKey="month" />
                                    <YAxis />
                                    <ChartTooltip
                                        content={<ChartTooltipContent />}
                                    />
                                    <Bar
                                        dataKey="amount"
                                        fill="var(--color-amount)"
                                        radius={[4, 4, 0, 0]}
                                    />
                                </BarChart>
                            </ChartContainer>
                        </CardContent>
                    </Card>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <Link href="/leave/approvals">
                        <Card className="h-full shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Plane className="h-4 w-4" />{' '}
                                    {t('pendingApprovals')}
                                </CardTitle>
                                <ArrowRight className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {reminders?.pendingLeaveCount ?? 0}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {t('leaveApprovals')}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>

                    <Link href="/contracts">
                        <Card className="h-full shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <FileSignature className="h-4 w-4" />{' '}
                                    {t('expiringSoon')}
                                </CardTitle>
                                <ArrowRight className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {reminders?.expiringContractsCount ?? 0}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {t('contracts')}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>

                    <Link href="/shifts/calendar">
                        <Card className="h-full shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <CalendarClock className="h-4 w-4" />{' '}
                                    {t('shifts')}
                                </CardTitle>
                                <ArrowRight className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {reminders?.todayShiftCount ?? 0}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {t('active')}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <div className="grid grid-cols-1 gap-4 md:grid-cols-3">
                    <Link href="/outsourcing">
                        <Card className="h-full shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <UsersRound className="h-4 w-4" />{' '}
                                    {t('outsourcedHeadcount')}
                                </CardTitle>
                                <ArrowRight className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {reminders?.outsourcedHeadcount ?? 0}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {t('placements')}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>

                    <Link href="/vendors">
                        <Card className="h-full shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <Building className="h-4 w-4" />{' '}
                                    {t('expiringAgreements')}
                                </CardTitle>
                                <ArrowRight className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {reminders?.expiringVendorAgreementsCount ??
                                        0}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {t('vendors')}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>

                    <Link href="/outsourcing/compliance">
                        <Card className="h-full shadow-sm transition-shadow hover:shadow-md">
                            <CardHeader className="flex flex-row items-center justify-between pb-2">
                                <CardTitle className="flex items-center gap-2 text-sm font-medium text-muted-foreground">
                                    <ShieldCheck className="h-4 w-4" />{' '}
                                    {t('openComplianceFlags')}
                                </CardTitle>
                                <ArrowRight className="h-4 w-4 text-muted-foreground" />
                            </CardHeader>
                            <CardContent>
                                <div className="text-2xl font-bold">
                                    {reminders?.openComplianceFlagsCount ?? 0}
                                </div>
                                <p className="mt-1 text-xs text-muted-foreground">
                                    {t('compliance')}
                                </p>
                            </CardContent>
                        </Card>
                    </Link>
                </div>

                <Card className="shadow-sm">
                    <CardHeader>
                        <CardTitle className="text-base">
                            {t('recentActivity')}
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        {recentActivity.length === 0 ? (
                            <p className="text-sm text-muted-foreground">
                                {t('noData')}
                            </p>
                        ) : (
                            <ul className="divide-y">
                                {recentActivity.map((item, idx) => {
                                    const Icon = activityIcon[item.type];
                                    const label =
                                        item.type === 'leave'
                                            ? t('leave')
                                            : item.type === 'hire'
                                              ? t('newHire')
                                              : t('payroll');

                                    return (
                                        <li
                                            key={`${item.type}-${idx}`}
                                            className="flex items-center gap-3 py-2.5"
                                        >
                                            <Icon className="h-4 w-4 text-muted-foreground" />
                                            <div className="flex-1">
                                                <p className="text-sm font-medium text-foreground">
                                                    {item.title}
                                                    <span className="ml-2 text-xs font-normal text-muted-foreground">
                                                        {label}
                                                        {item.detail
                                                            ? ` · ${item.detail}`
                                                            : ''}
                                                    </span>
                                                </p>
                                            </div>
                                            {item.status && (
                                                <Badge
                                                    variant="secondary"
                                                    className="text-xs"
                                                >
                                                    {item.status}
                                                </Badge>
                                            )}
                                            {item.timestamp && (
                                                <span className="text-xs text-muted-foreground">
                                                    {new Date(
                                                        item.timestamp,
                                                    ).toLocaleDateString(
                                                        'id-ID',
                                                    )}
                                                </span>
                                            )}
                                        </li>
                                    );
                                })}
                            </ul>
                        )}
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}



