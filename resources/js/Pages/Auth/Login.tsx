import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Card,
    CardContent,
    CardDescription,
    CardHeader,
    CardTitle,
} from '@/Components/ui/card';
import { useLanguage } from '@/i18n/LanguageContext';
import { Head, useForm } from '@inertiajs/react';
import { Building2, Globe } from 'lucide-react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { t, language, setLanguage } = useLanguage();
    const { data, setData, post, processing, errors, reset } = useForm({
        email: '',
        password: '',
        remember: false as boolean,
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('login'), {
            onFinish: () => reset('password'),
        });
    };

    return (
        <div className="flex min-h-screen items-center justify-center bg-muted/30 px-4">
            <Head title={t('signIn')} />

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
                        <span className="text-2xl font-bold text-foreground">
                            HRIS
                        </span>
                    </div>
                    <CardTitle className="text-xl">{t('welcomeBack')}</CardTitle>
                    <CardDescription>{t('signInDesc')}</CardDescription>
                </CardHeader>
                <CardContent>
                    {status && (
                        <p className="mb-4 text-sm font-medium text-green-600">
                            {status}
                        </p>
                    )}

                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="email">{t('emailAddress')}</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) => setData('email', e.target.value)}
                                placeholder="admin@company.com"
                                autoComplete="username"
                                required
                            />
                            {errors.email && (
                                <p className="text-sm text-destructive">
                                    {errors.email}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password">{t('password')}</Label>
                            <Input
                                id="password"
                                type="password"
                                value={data.password}
                                onChange={(e) =>
                                    setData('password', e.target.value)
                                }
                                autoComplete="current-password"
                                required
                            />
                            {errors.password && (
                                <p className="text-sm text-destructive">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <Button
                            type="submit"
                            className="w-full"
                            disabled={processing}
                        >
                            {processing ? t('loading') : t('signIn')}
                        </Button>

                        {canResetPassword && (
                            <p className="text-center text-sm text-muted-foreground">
                                <a
                                    href={route('password.request')}
                                    className="underline hover:text-foreground"
                                >
                                    Forgot password?
                                </a>
                            </p>
                        )}
                    </form>
                </CardContent>
            </Card>
        </div>
    );
}
