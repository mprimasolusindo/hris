import AuthShell, { AuthHead } from '@/Components/auth/AuthShell';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useLanguage } from '@/i18n/LanguageContext';
import { useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function Login({
    status,
    canResetPassword,
}: {
    status?: string;
    canResetPassword: boolean;
}) {
    const { t } = useLanguage();
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
        <>
            <AuthHead title={t('signIn')} />
            <AuthShell title={t('welcomeBack')} description={t('signInDesc')}>
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
            </AuthShell>
        </>
    );
}
