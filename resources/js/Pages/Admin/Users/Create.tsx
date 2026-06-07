import HrisLayout from '@/Layouts/HrisLayout';
import { Head, useForm } from '@inertiajs/react';
import { FormEvent } from 'react';
import { useLanguage } from '@/i18n/LanguageContext';
import { PageProps } from '@/types';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Checkbox } from '@/Components/ui/checkbox';

type RoleOption = { id: number; name: string; slug: string };

export default function Create({
    roles,
}: PageProps<{ roles: RoleOption[] }>) {
    const { t } = useLanguage();
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        roles: [] as number[],
    });

    const toggleRole = (roleId: number, checked: boolean) => {
        setData(
            'roles',
            checked
                ? [...data.roles, roleId]
                : data.roles.filter((id) => id !== roleId),
        );
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        post(route('admin.users.store'));
    };

    return (
        <HrisLayout>
            <Head title={t('addUser')} />

            <Card className="mx-auto max-w-2xl">
                <CardHeader>
                    <CardTitle>{t('addUser')}</CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit} className="space-y-4">
                        <div className="space-y-2">
                            <Label htmlFor="name">{t('name')}</Label>
                            <Input
                                id="name"
                                value={data.name}
                                onChange={(e) =>
                                    setData('name', e.target.value)
                                }
                            />
                            {errors.name && (
                                <p className="text-sm text-destructive">
                                    {errors.name}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="email">{t('email')}</Label>
                            <Input
                                id="email"
                                type="email"
                                value={data.email}
                                onChange={(e) =>
                                    setData('email', e.target.value)
                                }
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
                            />
                            {errors.password && (
                                <p className="text-sm text-destructive">
                                    {errors.password}
                                </p>
                            )}
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="password_confirmation">
                                {t('confirmPassword')}
                            </Label>
                            <Input
                                id="password_confirmation"
                                type="password"
                                value={data.password_confirmation}
                                onChange={(e) =>
                                    setData(
                                        'password_confirmation',
                                        e.target.value,
                                    )
                                }
                            />
                        </div>

                        <div className="space-y-2">
                            <Label>{t('roles')}</Label>
                            <div className="grid gap-2 sm:grid-cols-2">
                                {roles.map((role) => (
                                    <label
                                        key={role.id}
                                        className="flex items-center gap-2 rounded-md border p-3"
                                    >
                                        <Checkbox
                                            checked={data.roles.includes(
                                                role.id,
                                            )}
                                            onCheckedChange={(checked) =>
                                                toggleRole(
                                                    role.id,
                                                    checked === true,
                                                )
                                            }
                                        />
                                        <span>{role.name}</span>
                                    </label>
                                ))}
                            </div>
                            {errors.roles && (
                                <p className="text-sm text-destructive">
                                    {errors.roles}
                                </p>
                            )}
                        </div>

                        <div className="flex gap-2">
                            <Button type="submit" disabled={processing}>
                                {t('save')}
                            </Button>
                            <Button
                                type="button"
                                variant="outline"
                                onClick={() => window.history.back()}
                            >
                                {t('cancel')}
                            </Button>
                        </div>
                    </form>
                </CardContent>
            </Card>
        </HrisLayout>
    );
}
