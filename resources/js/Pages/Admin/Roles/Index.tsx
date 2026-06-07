import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import { Badge } from '@/Components/ui/badge';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { Plus, Pencil, Trash2 } from 'lucide-react';
import { useEffect } from 'react';
import { PageProps } from '@/types';
import { toast } from 'sonner';
import { useCan } from '@/hooks/useCan';

type RoleRow = {
    id: number;
    name: string;
    slug: string;
    description: string | null;
    is_system: boolean;
    users_count: number;
    permissions_count: number;
};

export default function Index({
    roles,
    flash,
}: PageProps<{ roles: RoleRow[] }>) {
    const { t } = useLanguage();
    const { can } = useCan();

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash?.success]);

    const handleDelete = (role: RoleRow) => {
        if (!confirm(t('confirmDeleteRole'))) {
            return;
        }
        router.delete(route('admin.roles.destroy', role.id));
    };

    return (
        <HrisLayout>
            <Head title={t('navRoles')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">
                        {t('navRoles')}
                    </h1>
                    {can('roles.create') && (
                        <Button asChild>
                            <Link href={route('admin.roles.create')}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addRole')}
                            </Link>
                        </Button>
                    )}
                </div>

                <Card>
                    <CardContent className="pt-6">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('name')}</TableHead>
                                    <TableHead>{t('slug')}</TableHead>
                                    <TableHead>{t('users')}</TableHead>
                                    <TableHead>{t('permissions')}</TableHead>
                                    <TableHead className="text-right">
                                        {t('actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {roles.map((role) => (
                                    <TableRow key={role.id}>
                                        <TableCell>
                                            <div className="flex items-center gap-2">
                                                <span className="font-medium">
                                                    {role.name}
                                                </span>
                                                {role.is_system && (
                                                    <Badge variant="outline">
                                                        {t('systemRole')}
                                                    </Badge>
                                                )}
                                            </div>
                                            {role.description && (
                                                <p className="text-sm text-muted-foreground">
                                                    {role.description}
                                                </p>
                                            )}
                                        </TableCell>
                                        <TableCell>
                                            <code className="text-xs">
                                                {role.slug}
                                            </code>
                                        </TableCell>
                                        <TableCell>{role.users_count}</TableCell>
                                        <TableCell>
                                            {role.permissions_count}
                                        </TableCell>
                                        <TableCell className="text-right">
                                            <div className="flex justify-end gap-2">
                                                {can('roles.update') && (
                                                    <Button
                                                        variant="outline"
                                                        size="icon"
                                                        asChild
                                                    >
                                                        <Link
                                                            href={route(
                                                                'admin.roles.edit',
                                                                role.id,
                                                            )}
                                                        >
                                                            <Pencil className="h-4 w-4" />
                                                        </Link>
                                                    </Button>
                                                )}
                                                {can('roles.delete') &&
                                                    !role.is_system && (
                                                        <Button
                                                            variant="outline"
                                                            size="icon"
                                                            onClick={() =>
                                                                handleDelete(
                                                                    role,
                                                                )
                                                            }
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    )}
                                            </div>
                                        </TableCell>
                                    </TableRow>
                                ))}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
