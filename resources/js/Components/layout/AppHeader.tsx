import {
    Bell,
    Search,
    LogOut,
    Globe,
    Plane,
    FileSignature,
    Building,
    ShieldCheck,
} from 'lucide-react';
import { Link, router, usePage } from '@inertiajs/react';
import { FormEventHandler, useState } from 'react';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import {
    DropdownMenu,
    DropdownMenuContent,
    DropdownMenuItem,
    DropdownMenuLabel,
    DropdownMenuSeparator,
    DropdownMenuTrigger,
} from '@/Components/ui/dropdown-menu';
import { Avatar, AvatarFallback } from '@/Components/ui/avatar';
import { Badge } from '@/Components/ui/badge';
import { SidebarTrigger } from '@/Components/ui/sidebar';
import { useLanguage } from '@/i18n/LanguageContext';
import { useReminders } from '@/hooks/useReminders';
import { PageProps } from '@/types';

export function AppHeader() {
    const { language, setLanguage, t } = useLanguage();
    const { auth } = usePage<PageProps>().props;
    const user = auth.user;
    const { data: reminders } = useReminders();
    const [searchTerm, setSearchTerm] = useState('');

    const submitSearch: FormEventHandler = (e) => {
        e.preventDefault();
        const q = searchTerm.trim();
        if (q === '') return;
        router.get(route('search.index'), { q });
    };

    const pendingLeaves = reminders?.pendingLeaveRequests ?? [];

    const initials =
        user?.name
            ?.split(' ')
            .map((n) => n[0])
            .join('')
            .slice(0, 2)
            .toUpperCase() ||
        user?.email?.slice(0, 2).toUpperCase() ||
        'AD';

    const totalAlerts =
        (reminders?.pendingLeaveCount || 0) +
        (reminders?.expiringContractsCount || 0) +
        (reminders?.expiringVendorAgreementsCount || 0) +
        (reminders?.openComplianceFlagsCount || 0);

    return (
        <header className="sticky top-0 z-30 flex h-14 items-center gap-4 border-b bg-background px-4">
            <SidebarTrigger className="shrink-0" />

            <form onSubmit={submitSearch} className="relative max-w-sm flex-1">
                <Search className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                <Input
                    placeholder={t('search')}
                    className="h-9 border-0 bg-muted/50 pl-8"
                    value={searchTerm}
                    onChange={(e) => setSearchTerm(e.target.value)}
                />
            </form>

            <div className="ml-auto flex items-center gap-2">
                <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => setLanguage(language === 'en' ? 'id' : 'en')}
                    className="gap-1.5 text-muted-foreground"
                >
                    <Globe className="h-4 w-4" />
                    <span className="text-xs font-medium">
                        {language === 'en' ? 'EN' : 'ID'}
                    </span>
                </Button>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button
                            variant="ghost"
                            size="icon"
                            className="relative text-muted-foreground"
                        >
                            <Bell className="h-4 w-4" />
                            {totalAlerts > 0 && (
                                <span className="absolute -right-0.5 -top-0.5 flex h-[18px] min-w-[18px] items-center justify-center rounded-full bg-destructive px-1 text-[10px] font-semibold text-destructive-foreground">
                                    {totalAlerts > 99 ? '99+' : totalAlerts}
                                </span>
                            )}
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end" className="w-80">
                        <DropdownMenuLabel>{t('notifications')}</DropdownMenuLabel>
                        <DropdownMenuSeparator />

                        <DropdownMenuLabel className="flex items-center gap-2 text-xs font-normal text-muted-foreground">
                            <Plane className="h-3 w-3" /> {t('pendingApprovals')}{' '}
                            <Badge variant="secondary" className="ml-auto">
                                {reminders?.pendingLeaveCount || 0}
                            </Badge>
                        </DropdownMenuLabel>
                        {pendingLeaves.length === 0 ? (
                            <DropdownMenuItem disabled className="text-xs">
                                {t('noData')}
                            </DropdownMenuItem>
                        ) : (
                            pendingLeaves.map((leave) => (
                                <DropdownMenuItem key={leave.id} asChild>
                                    <Link
                                        href="/leave/approvals"
                                        className="flex flex-col items-start gap-0.5"
                                    >
                                        <span className="text-sm">
                                            {leave.employee_name}
                                        </span>
                                        <span className="text-xs text-muted-foreground">
                                            {leave.start_date} — {leave.end_date}
                                        </span>
                                    </Link>
                                </DropdownMenuItem>
                            ))
                        )}

                        <DropdownMenuSeparator />
                        <DropdownMenuItem asChild>
                            <Link
                                href="/outsourcing/compliance"
                                className="flex items-center gap-2"
                            >
                                <ShieldCheck className="h-3.5 w-3.5" />
                                <span className="text-sm">{t('openComplianceFlags')}</span>
                                <Badge
                                    variant={
                                        reminders?.openComplianceFlagsCount
                                            ? 'destructive'
                                            : 'secondary'
                                    }
                                    className="ml-auto"
                                >
                                    {reminders?.openComplianceFlagsCount || 0}
                                </Badge>
                            </Link>
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>

                <DropdownMenu>
                    <DropdownMenuTrigger asChild>
                        <Button variant="ghost" size="icon" className="rounded-full">
                            <Avatar className="h-8 w-8">
                                <AvatarFallback className="bg-primary text-xs text-primary-foreground">
                                    {initials}
                                </AvatarFallback>
                            </Avatar>
                        </Button>
                    </DropdownMenuTrigger>
                    <DropdownMenuContent align="end">
                        <DropdownMenuItem className="text-xs text-muted-foreground">
                            {user?.email}
                        </DropdownMenuItem>
                        <DropdownMenuItem asChild>
                            <Link href={route('profile.edit')}>{t('profile')}</Link>
                        </DropdownMenuItem>
                        <DropdownMenuItem
                            className="text-destructive"
                            onClick={() => router.post(route('logout'))}
                        >
                            <LogOut className="mr-2 h-4 w-4" />
                            {t('logout')}
                        </DropdownMenuItem>
                    </DropdownMenuContent>
                </DropdownMenu>
            </div>
        </header>
    );
}
