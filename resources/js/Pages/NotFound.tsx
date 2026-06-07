import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';

export default function NotFound() {
    const { t } = useLanguage();

    return (
        <HrisLayout>
            <Head title="404" />

            <div className="flex min-h-[60vh] flex-col items-center justify-center text-center">
                <h1 className="text-4xl font-bold text-foreground">404</h1>
                <p className="mt-2 text-lg text-muted-foreground">
                    {t('pageNotFound')}
                </p>
                <Button className="mt-6" asChild>
                    <Link href={route('dashboard')}>{t('dashboard')}</Link>
                </Button>
            </div>
        </HrisLayout>
    );
}
