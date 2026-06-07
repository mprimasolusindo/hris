export interface User {
    id: number;
    name: string;
    email: string;
    email_verified_at?: string;
}

import type { ReminderSummary } from '@/hooks/useReminders';

export type BugReportRow = {
    id: number;
    title: string;
    description: string | null;
    status: string;
    url: string;
    page_title: string | null;
    screenshot_url: string | null;
    reported_by_name: string | null;
    created_at: string | null;
};

export type BugReportDetail = BugReportRow & {
    console_log: Array<{ level: string; timestamp: string; message: string }>;
    user_agent: string | null;
    viewport_width: number | null;
    viewport_height: number | null;
    updated_at: string | null;
};

export type PageProps<
    T extends Record<string, unknown> = Record<string, unknown>,
> = T & {
    auth: {
        user: User;
        roles: string[];
        permissions: string[];
    };
    flash: {
        success?: string | null;
    };
    reminders?: ReminderSummary | null;
    bugReport: {
        enabled: boolean;
    };
};
