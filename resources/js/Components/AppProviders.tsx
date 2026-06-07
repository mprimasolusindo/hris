import { PropsWithChildren } from 'react';
import { Toaster as Sonner } from '@/Components/ui/sonner';
import { TooltipProvider } from '@/Components/ui/tooltip';
import { LanguageProvider } from '@/i18n/LanguageContext';

export function AppProviders({ children }: PropsWithChildren) {
    return (
        <LanguageProvider>
            <TooltipProvider>
                {children}
                <Sonner />
            </TooltipProvider>
        </LanguageProvider>
    );
}
