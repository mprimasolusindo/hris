import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Button } from '@/Components/ui/button';
import { Card, CardContent } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import { Input } from '@/Components/ui/input';
import { Label } from '@/Components/ui/label';
import {
    Table,
    TableBody,
    TableCell,
    TableHead,
    TableHeader,
    TableRow,
} from '@/Components/ui/table';
import { PageProps } from '@/types';
import { FormEventHandler, useEffect, useState } from 'react';
import { Plus, Search } from 'lucide-react';
import { toast } from 'sonner';

type CandidateRow = {
    id: number;
    name: string;
    email: string | null;
    phone: string | null;
    application_count: number;
};

export default function Index({
    candidates,
    filters,
    summary,
    flash,
}: PageProps<{
    candidates: CandidateRow[];
    filters: { search: string };
    summary: { total: number };
}>) {
    const { t } = useLanguage();
    const [open, setOpen] = useState(false);
    const [search, setSearch] = useState(filters.search);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const form = useForm({
        name: '',
        email: '',
        phone: '',
    });

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        form.post(route('recruitment.candidates.store'), {
            onSuccess: () => {
                setOpen(false);
                form.reset();
            },
        });
    };

    const runSearch = () => {
        router.get(route('recruitment.candidates.index'), { search });
    };

    return (
        <HrisLayout>
            <Head title={t('candidates')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold text-foreground">{t('candidates')}</h1>
                    <Dialog open={open} onOpenChange={setOpen}>
                        <DialogTrigger asChild>
                            <Button>
                                <Plus className="mr-2 h-4 w-4" />
                                {t('addCandidate')}
                            </Button>
                        </DialogTrigger>
                        <DialogContent>
                            <DialogHeader>
                                <DialogTitle>{t('addCandidate')}</DialogTitle>
                            </DialogHeader>
                            <form onSubmit={submit} className="space-y-4">
                                <div className="space-y-2">
                                    <Label>{t('name')}</Label>
                                    <Input
                                        value={form.data.name}
                                        onChange={(e) => form.setData('name', e.target.value)}
                                        required
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('email')}</Label>
                                    <Input
                                        type="email"
                                        value={form.data.email}
                                        onChange={(e) => form.setData('email', e.target.value)}
                                    />
                                </div>
                                <div className="space-y-2">
                                    <Label>{t('phone')}</Label>
                                    <Input
                                        value={form.data.phone}
                                        onChange={(e) => form.setData('phone', e.target.value)}
                                    />
                                </div>
                                <Button type="submit" disabled={form.processing}>
                                    {t('save')}
                                </Button>
                            </form>
                        </DialogContent>
                    </Dialog>
                </div>

                <Card>
                    <CardContent className="p-4">
                        <p className="text-xs text-muted-foreground">{t('all')}</p>
                        <p className="text-2xl font-semibold">{summary.total}</p>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="flex gap-2 p-4">
                        <Input
                            placeholder={t('search')}
                            value={search}
                            onChange={(e) => setSearch(e.target.value)}
                            onKeyDown={(e) => e.key === 'Enter' && runSearch()}
                        />
                        <Button type="button" variant="secondary" onClick={runSearch}>
                            <Search className="h-4 w-4" />
                        </Button>
                    </CardContent>
                </Card>

                <Card>
                    <CardContent className="p-0">
                        <Table>
                            <TableHeader>
                                <TableRow>
                                    <TableHead>{t('name')}</TableHead>
                                    <TableHead>{t('email')}</TableHead>
                                    <TableHead>{t('phone')}</TableHead>
                                    <TableHead>{t('applications')}</TableHead>
                                </TableRow>
                            </TableHeader>
                            <TableBody>
                                {candidates.length === 0 ? (
                                    <TableRow>
                                        <TableCell
                                            colSpan={4}
                                            className="py-8 text-center text-muted-foreground"
                                        >
                                            {t('noData')}
                                        </TableCell>
                                    </TableRow>
                                ) : (
                                    candidates.map((row) => (
                                        <TableRow key={row.id}>
                                            <TableCell>
                                                <Link
                                                    href={route(
                                                        'recruitment.candidates.show',
                                                        row.id,
                                                    )}
                                                    className="font-medium text-primary hover:underline"
                                                >
                                                    {row.name}
                                                </Link>
                                            </TableCell>
                                            <TableCell>{row.email}</TableCell>
                                            <TableCell>{row.phone}</TableCell>
                                            <TableCell>{row.application_count}</TableCell>
                                        </TableRow>
                                    ))
                                )}
                            </TableBody>
                        </Table>
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
