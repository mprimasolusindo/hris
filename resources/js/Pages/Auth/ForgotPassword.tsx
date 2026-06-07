import AuthShell, { AuthHead } from '@/Components/auth/AuthShell';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { useLanguage } from '@/i18n/LanguageContext';
import { Link, useForm } from '@inertiajs/react';
import { FormEventHandler } from 'react';

export default function ForgotPassword({ status }: { status?: string }) {
    const { t } = useLanguage();
    const { data, setData, post, processing, errors } = useForm({
        email: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        post(route('password.email'));
    };

    return (
        <>
            <AuthHead title={t('resetPassword')} />
            <AuthShell title={t('resetPassword')} description={t('resetPasswordDesc')}>
                {status && (
                    <p className="mb-4 text-sm font-medium text-green-600">{status}</p>
                )}

                <form onSubmit={submit} className="space-y-4">
                    <div className="space-y-2">
                        <Label htmlFor="email">{t('emailAddress')}</Label>
                        <Input
                            id="email"
                            type="email"
                            value={data.email}
                            onChange={(e) => setData('email', e.target.value)}
                            required
                            autoFocus
                        />
                        {errors.email && (
                            <p className="text-sm text-destructive">{errors.email}</p>
                        )}
                    </div>

                    <Button type="submit" className="w-full" disabled={processing}>
                        {processing ? t('loading') : t('sendResetLink')}
                    </Button>

                    <p className="text-center text-sm text-muted-foreground">
                        <Link href={route('login')} className="underline hover:text-foreground">
                            {t('backToSignIn')}
                        </Link>
                    </p>
                </form>
            </AuthShell>
        </>
    );
}
