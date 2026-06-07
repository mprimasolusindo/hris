import { PropsWithChildren } from 'react';
import { SidebarProvider } from '@/Components/ui/sidebar';
import { AppSidebar } from '@/Components/layout/AppSidebar';
import { AppHeader } from '@/Components/layout/AppHeader';
import BugReportWidget from '@/Features/BugReport/BugReportWidget';

export default function HrisLayout({ children }: PropsWithChildren) {
    return (
        <SidebarProvider>
            <div className="flex min-h-screen w-full">
                <AppSidebar />
                <div className="flex flex-1 flex-col">
                    <AppHeader />
                    <main className="flex-1 bg-muted/30 p-6">{children}</main>
                </div>
            </div>
            <BugReportWidget />
        </SidebarProvider>
    );
}
