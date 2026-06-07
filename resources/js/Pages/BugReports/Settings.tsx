import HrisLayout from '@/Layouts/HrisLayout';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/Components/ui/card';
import { Label } from '@/Components/ui/label';
import { Switch } from '@/Components/ui/switch';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import { Head, Link, useForm } from '@inertiajs/react';
import { ArrowLeft, Bug } from 'lucide-react';
import { FormEventHandler, useEffect } from 'react';
import { toast } from 'sonner';

export default function Settings({
    enabled,
    flash,
}: PageProps<{ enabled: boolean }>) {
    const { t } = useLanguage();
    const form = useForm({ enabled });

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.put(route('bug-reports.settings.update'));
    };

    return (
        <HrisLayout>
            <Head title={t('bugReportSettings')} />

            <div className="mx-auto max-w-2xl space-y-6">
                <div className="flex items-center gap-3">
                    <Button variant="ghost" size="icon" asChild>
                        <Link href={route('bug-reports.index')}>
                            <ArrowLeft className="h-4 w-4" />
                        </Link>
                    </Button>
                    <div className="flex items-center gap-2">
                        <Bug className="h-6 w-6 text-primary" />
                        <h1 className="text-2xl font-bold text-foreground">{t('bugReportSettings')}</h1>
                    </div>
                </div>

                <Card>
                    <CardHeader>
                        <CardTitle>{t('enableBugReportWidget')}</CardTitle>
                        <CardDescription>{t('bugReportWidgetDescription')}</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <form onSubmit={submit} className="space-y-6">
                            <div className="flex items-center justify-between gap-4">
                                <Label htmlFor="enabled">{t('enableBugReportWidget')}</Label>
                                <Switch
                                    id="enabled"
                                    checked={form.data.enabled}
                                    onCheckedChange={(checked) => form.setData('enabled', checked)}
                                />
                            </div>
                            <Button type="submit" disabled={form.processing}>
                                {t('save')}
                            </Button>
                        </form>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
