import { PropsWithChildren } from 'react';
import { SidebarProvider } from '@/Components/ui/sidebar';
import { AppSidebar } from '@/Components/layout/AppSidebar';
import { AppHeader } from '@/Components/layout/AppHeader';
import BugReportWidget from '@/Features/BugReport/BugReportWidget';

export default function HrisLayout({ children }: PropsWithChildren) {
    return (
        <>
            <div className="app-atmosphere" aria-hidden="true" />
            <SidebarProvider className="relative z-[1] bg-transparent">
                <AppSidebar />
                <div className="flex min-h-svh flex-1 flex-col">
                    <AppHeader />
                    <main className="flex-1 bg-transparent p-6">{children}</main>
                </div>
            </SidebarProvider>
            <BugReportWidget />
        </>
    );
}
