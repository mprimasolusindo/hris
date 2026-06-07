import { Button } from '@/Components/ui/button';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { useLanguage } from '@/i18n/LanguageContext';
import { Head } from '@inertiajs/react';
import { Building2, Globe } from 'lucide-react';
import { ReactNode } from 'react';

type AuthShellProps = {
    title: string;
    description?: string;
    children: ReactNode;
};

export default function AuthShell({ title, description, children }: AuthShellProps) {
    const { language, setLanguage } = useLanguage();

    return (
        <div className="flex min-h-screen items-center justify-center bg-muted/30 px-4">
            <div className="absolute right-4 top-4">
                <Button
                    variant="ghost"
                    size="sm"
                    onClick={() => setLanguage(language === 'en' ? 'id' : 'en')}
                >
                    <Globe className="mr-1 h-4 w-4" />
                    {language === 'en' ? 'EN' : 'ID'}
                </Button>
            </div>

            <Card className="w-full max-w-md shadow-lg">
                <CardHeader className="space-y-2 text-center">
                    <div className="mx-auto flex items-center justify-center gap-2">
                        <Building2 className="h-8 w-8 text-primary" />
                        <span className="text-2xl font-bold text-foreground">HRIS</span>
                    </div>
                    <CardTitle className="text-xl">{title}</CardTitle>
                    {description && <CardDescription>{description}</CardDescription>}
                </CardHeader>
                <CardContent>{children}</CardContent>
            </Card>
        </div>
    );
}

export function AuthHead({ title }: { title: string }) {
    return <Head title={title} />;
}
