import { useEffect, useMemo, useState } from 'react';
import {
    LayoutDashboard,
    Users,
    Clock,
    Building2,
    Wallet,
    Plane,
    Building,
    Briefcase as JobIcon,
    Target,
    Bug,
    ChevronRight,
    Shield,
    CreditCard,
} from 'lucide-react';
import { NavLink } from '@/Components/NavLink';
import { usePage } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { useCan } from '@/hooks/useCan';
import {
    Sidebar,
    SidebarContent,
    SidebarGroup,
    SidebarGroupContent,
    SidebarGroupLabel,
    SidebarMenu,
    SidebarMenuButton,
    SidebarMenuItem,
    SidebarMenuSub,
    SidebarMenuSubButton,
    SidebarMenuSubItem,
    useSidebar,
} from '@/Components/ui/sidebar';
import {
    Collapsible,
    CollapsibleContent,
    CollapsibleTrigger,
} from '@/Components/ui/collapsible';
import { Badge } from '@/Components/ui/badge';
import { useReminders } from '@/hooks/useReminders';

type IconType = React.ComponentType<{ className?: string }>;

type NavChild = {
    title: string;
    href: string;
    permission: string;
    badge?: number;
};

type NavLeaf = {
    title: string;
    href: string;
    icon: IconType;
    permission: string;
    badge?: number;
};

type NavParent = {
    title: string;
    icon: IconType;
    children: NavChild[];
};

type NavNode = NavLeaf | NavParent;

function isNavParent(node: NavNode): node is NavParent {
    return 'children' in node;
}

function aggregateBadge(node: NavParent): number {
    return node.children.reduce((sum, child) => sum + (child.badge ?? 0), 0);
}

export function AppSidebar() {
    const { state } = useSidebar();
    const collapsed = state === 'collapsed';
    const { t } = useLanguage();
    const { can } = useCan();
    const { url } = usePage();
    const { data: reminders } = useReminders();
    const [expanded, setExpanded] = useState<Set<string>>(new Set());

    const primaryNav: NavNode[] = useMemo(
        () => [
            {
                title: t('dashboard'),
                href: route('dashboard'),
                icon: LayoutDashboard,
                permission: 'dashboard.view',
            },
            {
                title: t('employees'),
                icon: Users,
                children: [
                    {
                        title: t('directory'),
                        href: route('employees.index'),
                        permission: 'employees.view',
                    },
                    {
                        title: t('contracts'),
                        href: route('contracts.index'),
                        permission: 'contracts.view',
                        badge: reminders?.expiringContractsCount || 0,
                    },
                ],
            },
            {
                title: t('navTimeAttendance'),
                icon: Clock,
                children: [
                    {
                        title: t('attendance'),
                        href: route('attendance.index'),
                        permission: 'attendance.view',
                    },
                    {
                        title: t('shifts'),
                        href: route('shifts.index'),
                        permission: 'shifts.view',
                    },
                    {
                        title: t('overtime'),
                        href: route('overtime.index'),
                        permission: 'overtime.view',
                    },
                ],
            },
            {
                title: t('leaveNav'),
                icon: Plane,
                children: [
                    {
                        title: t('leaveRequests'),
                        href: route('leave.index'),
                        permission: 'leave.view',
                    },
                    {
                        title: t('leaveApprovals'),
                        href: route('leave.approvals.index'),
                        permission: 'leave.approvals.view',
                        badge: reminders?.pendingLeaveCount || 0,
                    },
                    {
                        title: t('leaveBalance'),
                        href: route('leave.balance.index'),
                        permission: 'leave.balance.view',
                    },
                    {
                        title: t('leaveTypes'),
                        href: route('leave.types.index'),
                        permission: 'leave.types.view',
                    },
                ],
            },
            {
                title: t('payroll'),
                icon: Wallet,
                children: [
                    {
                        title: t('payrollRuns'),
                        href: route('payroll.index'),
                        permission: 'payroll.view',
                    },
                    {
                        title: t('allowanceTypes'),
                        href: route('master.allowance-types.index'),
                        permission: 'master.allowance-types.view',
                    },
                    {
                        title: t('masterAllowances'),
                        href: route('payroll.master-allowances.index'),
                        permission: 'payroll.master-allowances.view',
                    },
                    {
                        title: t('masterDeductions'),
                        href: route('payroll.master-deductions.index'),
                        permission: 'payroll.master-deductions.view',
                    },
                    {
                        title: t('bpjsConfig'),
                        href: route('payroll.bpjs-config.index'),
                        permission: 'payroll.bpjs-config.view',
                    },
                    {
                        title: t('taxRules'),
                        href: route('payroll.tax-rules.index'),
                        permission: 'payroll.tax-rules.view',
                    },
                ],
            },
            {
                title: t('navRecruitment'),
                icon: JobIcon,
                children: [
                    {
                        title: t('jobs'),
                        href: route('recruitment.jobs.index'),
                        permission: 'recruitment.jobs.view',
                    },
                    {
                        title: t('candidates'),
                        href: route('recruitment.candidates.index'),
                        permission: 'recruitment.candidates.view',
                    },
                    {
                        title: t('pipeline'),
                        href: route('recruitment.pipeline.index'),
                        permission: 'recruitment.pipeline.view',
                    },
                    {
                        title: t('interviews'),
                        href: route('recruitment.interviews.index'),
                        permission: 'recruitment.interviews.view',
                    },
                ],
            },
            {
                title: t('navTalent'),
                icon: Target,
                children: [
                    {
                        title: t('performance'),
                        href: route('performance.index'),
                        permission: 'talent.performance.view',
                    },
                    {
                        title: t('training'),
                        href: route('training.index'),
                        permission: 'talent.training.view',
                    },
                    {
                        title: t('talentPool'),
                        href: route('talent-pool.index'),
                        permission: 'talent.talent-pool.view',
                    },
                    {
                        title: t('succession'),
                        href: route('succession.index'),
                        permission: 'talent.succession.view',
                    },
                    {
                        title: t('nineBox'),
                        href: route('succession.nine-box.index'),
                        permission: 'talent.nine-box.view',
                    },
                ],
            },
            {
                title: t('outsourcing'),
                icon: Building,
                children: [
                    {
                        title: t('vendors'),
                        href: route('vendors.index'),
                        permission: 'vendors.view',
                        badge: reminders?.expiringVendorAgreementsCount || 0,
                    },
                    {
                        title: t('placements'),
                        href: route('outsourcing.index'),
                        permission: 'outsourcing.view',
                    },
                    {
                        title: t('placementTracking'),
                        href: route('outsourcing.tracking.index'),
                        permission: 'outsourcing.tracking.view',
                    },
                    {
                        title: t('vendorBilling'),
                        href: route('vendor-billing.index'),
                        permission: 'vendor-billing.view',
                    },
                    {
                        title: t('compliance'),
                        href: route('outsourcing.compliance.index'),
                        permission: 'outsourcing.compliance.view',
                        badge: reminders?.openComplianceFlagsCount || 0,
                    },
                ],
            },
        ],
        [t, reminders],
    );

    const adminNav: NavNode[] = useMemo(
        () => [
            {
                title: t('organization'),
                icon: Building2,
                children: [
                    {
                        title: t('companies'),
                        href: route('organization.companies.index'),
                        permission: 'organization.companies.view',
                    },
                    {
                        title: t('sites'),
                        href: route('organization.sites.index'),
                        permission: 'organization.sites.view',
                    },
                    {
                        title: t('departments'),
                        href: route('organization.departments.index'),
                        permission: 'organization.departments.view',
                    },
                    {
                        title: t('positions'),
                        href: route('organization.positions.index'),
                        permission: 'organization.positions.view',
                    },
                ],
            },
            {
                title: t('navSystemAccess'),
                icon: Shield,
                children: [
                    {
                        title: t('navUsers'),
                        href: route('admin.users.index'),
                        permission: 'users.view',
                    },
                    {
                        title: t('navRoles'),
                        href: route('admin.roles.index'),
                        permission: 'roles.view',
                    },
                ],
            },
            {
                title: t('saasBilling'),
                icon: CreditCard,
                children: [
                    {
                        title: t('tenants'),
                        href: route('admin.saas.tenants.index'),
                        permission: 'saas.tenants.view',
                    },
                    {
                        title: t('plans'),
                        href: route('admin.saas.plans.index'),
                        permission: 'saas.plans.view',
                    },
                    {
                        title: t('subscriptions'),
                        href: route('admin.saas.subscriptions.index'),
                        permission: 'saas.subscriptions.view',
                    },
                    {
                        title: t('payments'),
                        href: route('admin.saas.payments.index'),
                        permission: 'saas.payments.view',
                    },
                ],
            },
            {
                title: t('bugReports'),
                href: route('bug-reports.index'),
                icon: Bug,
                permission: 'bug-reports.view',
            },
        ],
        [t],
    );

    const filterNav = (nodes: NavNode[]): NavNode[] =>
        nodes
            .map((node) => {
                if (isNavParent(node)) {
                    const children = node.children.filter((child) =>
                        can(child.permission),
                    );
                    if (children.length === 0) {
                        return null;
                    }
                    return { ...node, children };
                }
                return can(node.permission) ? node : null;
            })
            .filter((node): node is NavNode => node !== null);

    const visiblePrimaryNav = useMemo(
        () => filterNav(primaryNav),
        [primaryNav, can],
    );
    const visibleAdminNav = useMemo(
        () => filterNav(adminNav),
        [adminNav, can],
    );

    const isActive = (path: string) =>
        url === path || url.startsWith(path + '/');

    const hasActiveChild = (node: NavParent) =>
        node.children.some((child) => isActive(child.href));

    useEffect(() => {
        setExpanded((prev) => {
            const next = new Set(prev);
            [...visiblePrimaryNav, ...visibleAdminNav].forEach((node) => {
                if (isNavParent(node) && hasActiveChild(node)) {
                    next.add(node.title);
                }
            });
            return next;
        });
        // eslint-disable-next-line react-hooks/exhaustive-deps
    }, [url, reminders, visiblePrimaryNav, visibleAdminNav]);

    const toggleExpanded = (title: string, open: boolean) => {
        setExpanded((prev) => {
            const next = new Set(prev);
            if (open) {
                next.add(title);
            } else {
                next.delete(title);
            }
            return next;
        });
    };

    const renderBadge = (count: number) =>
        count > 0 ? (
            <Badge
                variant="destructive"
                className="ml-auto h-5 px-1.5 text-[10px]"
            >
                {count}
            </Badge>
        ) : null;

    const renderLeaf = (item: NavLeaf) => (
        <SidebarMenuItem key={item.href}>
            <SidebarMenuButton asChild tooltip={item.title}>
                <NavLink
                    href={item.href}
                    className="hover:bg-sidebar-accent/50"
                    active={isActive(item.href)}
                    activeClassName="bg-sidebar-accent text-sidebar-accent-foreground font-medium"
                >
                    <item.icon className="h-4 w-4" />
                    {!collapsed && <span className="flex-1">{item.title}</span>}
                    {!collapsed && renderBadge(item.badge ?? 0)}
                </NavLink>
            </SidebarMenuButton>
        </SidebarMenuItem>
    );

    const renderParent = (node: NavParent) => {
        const activeChild = hasActiveChild(node);
        const badgeCount = aggregateBadge(node);
        const isOpen = expanded.has(node.title);

        return (
            <Collapsible
                key={node.title}
                open={isOpen}
                onOpenChange={(open) => toggleExpanded(node.title, open)}
                className="group/collapsible"
            >
                <SidebarMenuItem>
                    <CollapsibleTrigger asChild>
                        <SidebarMenuButton
                            tooltip={node.title}
                            isActive={activeChild || isOpen}
                        >
                            <node.icon className="h-4 w-4" />
                            {!collapsed && (
                                <span className="flex-1">{node.title}</span>
                            )}
                            {!collapsed && !isOpen && renderBadge(badgeCount)}
                            {!collapsed && (
                                <ChevronRight className="ml-auto h-4 w-4 transition-transform group-data-[state=open]/collapsible:rotate-90" />
                            )}
                        </SidebarMenuButton>
                    </CollapsibleTrigger>
                    <CollapsibleContent>
                        <SidebarMenuSub>
                            {node.children.map((child) => (
                                <SidebarMenuSubItem key={child.href}>
                                    <SidebarMenuSubButton
                                        asChild
                                        isActive={isActive(child.href)}
                                    >
                                        <NavLink
                                            href={child.href}
                                            className="hover:bg-sidebar-accent/50"
                                            active={isActive(child.href)}
                                            activeClassName="bg-sidebar-accent text-sidebar-accent-foreground font-medium"
                                        >
                                            <span className="flex-1">
                                                {child.title}
                                            </span>
                                            {renderBadge(child.badge ?? 0)}
                                        </NavLink>
                                    </SidebarMenuSubButton>
                                </SidebarMenuSubItem>
                            ))}
                        </SidebarMenuSub>
                    </CollapsibleContent>
                </SidebarMenuItem>
            </Collapsible>
        );
    };

    const renderNode = (node: NavNode) =>
        isNavParent(node) ? renderParent(node) : renderLeaf(node);

    const renderNavGroup = (label: string | null, items: NavNode[]) => {
        if (items.length === 0) {
            return null;
        }

        return (
            <SidebarGroup>
                {label && (
                    <SidebarGroupLabel>
                        {!collapsed ? label : ''}
                    </SidebarGroupLabel>
                )}
                <SidebarGroupContent>
                    <SidebarMenu>{items.map(renderNode)}</SidebarMenu>
                </SidebarGroupContent>
            </SidebarGroup>
        );
    };

    return (
        <Sidebar collapsible="icon">
            <SidebarContent>
                <div className="flex items-center gap-2 px-4 py-4">
                    <Building2 className="h-6 w-6 text-primary" />
                    {!collapsed && (
                        <span className="text-lg font-bold text-foreground">
                            HRIS
                        </span>
                    )}
                </div>

                {renderNavGroup(null, visiblePrimaryNav)}
                {renderNavGroup(t('navAdmin'), visibleAdminNav)}
            </SidebarContent>
        </Sidebar>
    );
}
