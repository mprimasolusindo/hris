import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Input } from '@/Components/ui/input';
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
import { Plus, Search, Pencil, Trash2 } from 'lucide-react';
import { FormEvent, useEffect, useState } from 'react';
import { PageProps } from '@/types';
import { toast } from 'sonner';
import { useCan } from '@/hooks/useCan';

type UserRow = {
    id: number;
    name: string;
    email: string;
    roles: Array<{ id: number; name: string; slug: string }>;
    created_at: string | null;
};

type PaginatedUsers = {
    data: UserRow[];
    links: Array<{ url: string | null; label: string; active: boolean }>;
    current_page: number;
    last_page: number;
};

export default function Index({
    users,
    filters,
    flash,
}: PageProps<{
    users: PaginatedUsers;
    filters: { search: string };
}>) {
    const { t } = useLanguage();
    const { can } = useCan();
    const [search, setSearch] = useState(filters.search || '');

    useEffect(() => {
        if (flash?.success) {
            toast.success(flash.success);
        }
    }, [flash?.success]);

    const applySearch = (e?: FormEvent) => {
        e?.preventDefault();
        router.get(
            route('admin.users.index'),
            { search: search || undefined },
            { preserveState: true, replace: true },
        );
    };

    const handleDelete = (user: UserRow) => {
        if (!confirm(t('confirmDeleteUser'))) {
            return;
        }
        router.delete(route('admin.users.destroy', user.id));
    };

    return (
        <HrisLayout>
            <Head title={t('navUsers')} />

            <div className="space-y-6">
                <div className="flex items-center justify-between">
                    <h1 className="text-2xl font-bold text-foreground">
                        {t('navUsers')}
                    </h1>
                    {can('users.create') && (
                        <Button asChild>
                            <Link href={route('admin.users.create')}>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addUser')}
                            </Link>
                        </Button>
                    )}
                </div>

                <Card>
                    <CardContent className="pt-6">
                        <form
                            onSubmit={applySearch}
                            className="mb-4 flex gap-2"
                        >
                            <div className="relative flex-1">
                                <Search className="absolute left-3 top-1/2 h-4 w-4 -translate-y-1/2 text-muted-foreground" />
                                <Input
                                    value={search}
                                    onChange={(e) => setSearch(e.target.value)}
                                    placeholder={t('searchUsers')}
                                    className="pl-9"
                                />
                            </div>
                            <Button type="submit" variant="secondary">
                                {t('search')}
                            </Button>
                        </form>

                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('name')}</TableHead>
                                    <TableHead>{t('email')}</TableHead>
                                    <TableHead>{t('roles')}</TableHead>
                                    <TableHead className="text-right">
                                        {t('actions')}
                                    </TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {users.data.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="text-center text-muted-foreground"
                                        >
                                            {t('noUsersFound')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    users.data.map((user) => (
                                        <TableRow key={user.id}>
                                            <TableCell className="font-medium">
                                                {user.name}
                                            </TableCell>
                                            <TableCell>{user.email}</TableCell>
                                            <TableCell>
                                                <div className="flex flex-wrap gap-1">
                                                    {user.roles.map((role) => (
                                                        <Badge
                                                            key={role.id}
                                                            variant="secondary"
                                                        >
                                                            {role.name}
                                                        </Badge>
                                                    ))}
                                                </div>
                                            </TableCell>
                                            <TableCell className="text-right">
                                                <div className="flex justify-end gap-2">
                                                    {can('users.update') && (
                                                        <Button
                                                            variant="outline"
                                                            size="icon"
                                                            asChild
                                                        >
                                                            <Link
                                                                href={route(
                                                                    'admin.users.edit',
                                                                    user.id,
                                                                )}
                                                            >
                                                                <Pencil className="h-4 w-4" />
                                                            </Link>
                                                        </Button>
                                                    )}
                                                    {can('users.delete') && (
                                                        <Button
                                                            variant="outline"
                                                            size="icon"
                                                            onClick={() =>
                                                                handleDelete(
                                                                    user,
                                                                )
                                                            }
                                                        >
                                                            <Trash2 className="h-4 w-4" />
                                                        </Button>
                                                    )}
                                                </div>
                                            </TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>

                        {users.last_page > 1 && (
                            <div className="mt-4 flex flex-wrap gap-2">
                                {users.links.map((link, index) => (
                                    <Button
                                        key={index}
                                        variant={
                                            link.active ? 'default' : 'outline'
                                        }
                                        size="sm"
                                        disabled={!link.url}
                                        onClick={() =>
                                            link.url &&
                                            router.get(link.url, {}, {
                                                preserveState: true,
                                            })
                                        }
                                        dangerouslySetInnerHTML={{
                                            __html: link.label,
                                        }}
                                    />
                                ))}
                            </div>
                        )}
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
