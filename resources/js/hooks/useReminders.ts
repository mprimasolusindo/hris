import { PageProps } from '@/types';
import { usePage } from '@inertiajs/react';

export interface ReminderSummary {
    pendingLeaveCount: number;
    expiringContractsCount: number;
    todayShiftCount: number;
    outsourcedHeadcount: number;
    expiringVendorAgreementsCount: number;
    openComplianceFlagsCount: number;
    expiringContracts: Array<{
        id: string;
        employee_name: string;
        contract_number: string;
        days_left: number;
    }>;
    expiringVendorAgreements: Array<{
        id: string;
        vendor_name: string;
        contract_number: string;
        days_left: number;
    }>;
    pendingLeaveRequests: Array<{
        id: string;
        employee_name: string;
        start_date: string;
        end_date: string;
        created_at?: string;
    }>;
}

const emptyReminders: ReminderSummary = {
    pendingLeaveCount: 0,
    expiringContractsCount: 0,
    todayShiftCount: 0,
    outsourcedHeadcount: 0,
    expiringVendorAgreementsCount: 0,
    openComplianceFlagsCount: 0,
    expiringContracts: [],
    expiringVendorAgreements: [],
    pendingLeaveRequests: [],
};

export function useReminders() {
    const { reminders } = usePage<PageProps>().props;

    return {
        data: (reminders as ReminderSummary | null | undefined) ?? emptyReminders,
    };
}
