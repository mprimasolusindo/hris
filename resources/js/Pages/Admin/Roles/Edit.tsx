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
import { Textarea } from '@/Components/ui/textarea';

type PermissionItem = { id: number; key: string; name: string };

type RoleForm = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_system: boolean;
    permission_ids: number[];
} | null;

export default function Edit({
    role,
    permissionGroups,
}: PageProps<{
    role: RoleForm;
    permissionGroups: Record<string, PermissionItem[]>;
}>) {
    const { t } = useLanguage();
    const isEdit = role !== null;

    const { data, setData, post, put, processing, errors } = useForm({
        name: role?.name ?? '',
        slug: role?.slug ?? '',
        description: role?.description ?? '',
        permissions: role?.permission_ids ?? ([] as number[]),
    });

    const togglePermission = (permissionId: number, checked: boolean) => {
        setData(
            'permissions',
            checked
                ? [...data.permissions, permissionId]
                : data.permissions.filter((id) => id !== permissionId),
        );
    };

    const toggleModule = (modulePermissions: PermissionItem[], checked: boolean) => {
        const ids = modulePermissions.map((p) => p.id);
        if (checked) {
            setData('permissions', [...new Set([...data.permissions, ...ids])]);
        } else {
            setData(
                'permissions',
                data.permissions.filter((id) => !ids.includes(id)),
            );
        }
    };

    const submit = (e: FormEvent) => {
        e.preventDefault();
        if (isEdit && role) {
            put(route('admin.roles.update', role.id));
        } else {
            post(route('admin.roles.store'));
        }
    };

    return (
        <HrisLayout>
            <Head title={isEdit ? t('editRole') : t('addRole')} />

            <Card className="mx-auto max-w-4xl">
                <CardHeader>
                    <CardTitle>
                        {isEdit ? t('editRole') : t('addRole')}
                    </CardTitle>
                </CardHeader>
                <CardContent>
                    <form onSubmit={submit} className="space-y-6">
                        <div className="grid gap-4 sm:grid-cols-2">
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
                                <Label htmlFor="slug">{t('slug')}</Label>
                                <Input
                                    id="slug"
                                    value={data.slug}
                                    onChange={(e) =>
                                        setData('slug', e.target.value)
                                    }
                                    disabled={role?.is_system}
                                />
                                {errors.slug && (
                                    <p className="text-sm text-destructive">
                                        {errors.slug}
                                    </p>
                                )}
                            </div>
                        </div>

                        <div className="space-y-2">
                            <Label htmlFor="description">
                                {t('description')}
                            </Label>
                            <Textarea
                                id="description"
                                value={data.description}
                                onChange={(e) =>
                                    setData('description', e.target.value)
                                }
                                rows={2}
                            />
                        </div>

                        <div className="space-y-4">
                            <Label>{t('permissions')}</Label>
                            {Object.entries(permissionGroups).map(
                                ([module, permissions]) => {
                                    const allChecked = permissions.every((p) =>
                                        data.permissions.includes(p.id),
                                    );
                                    const someChecked = permissions.some((p) =>
                                        data.permissions.includes(p.id),
                                    );

                                    return (
                                        <div
                                            key={module}
                                            className="rounded-lg border p-4"
                                        >
                                            <div className="mb-3 flex items-center gap-2">
                                                <Checkbox
                                                    checked={allChecked}
                                                    onCheckedChange={(checked) =>
                                                        toggleModule(
                                                            permissions,
                                                            checked === true,
                                                        )
                                                    }
                                                    className={
                                                        someChecked && !allChecked
                                                            ? 'data-[state=checked]:bg-muted'
                                                            : ''
                                                    }
                                                />
                                                <span className="font-medium capitalize">
                                                    {module.replace(/\./g, ' › ')}
                                                </span>
                                            </div>
                                            <div className="grid gap-2 sm:grid-cols-2 lg:grid-cols-3">
                                                {permissions.map((permission) => (
                                                    <label
                                                        key={permission.id}
                                                        className="flex items-center gap-2 text-sm"
                                                    >
                                                        <Checkbox
                                                            checked={data.permissions.includes(
                                                                permission.id,
                                                            )}
                                                            onCheckedChange={(
                                                                checked,
                                                            ) =>
                                                                togglePermission(
                                                                    permission.id,
                                                                    checked ===
                                                                        true,
                                                                )
                                                            }
                                                        />
                                                        <span>
                                                            {permission.name}
                                                        </span>
                                                    </label>
                                                ))}
                                            </div>
                                        </div>
                                    );
                                },
                            )}
                            {errors.permissions && (
                                <p className="text-sm text-destructive">
                                    {errors.permissions}
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
