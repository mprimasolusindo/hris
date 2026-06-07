import HrisLayout from '@/Layouts/HrisLayout';
import { Head, router, useForm } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Badge } from '@/Components/ui/badge';
import { Button } from '@/Components/ui/button';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import {
    Dialog,
    DialogContent,
    DialogHeader,
    DialogTitle,
    DialogTrigger,
} from '@/Components/ui/dialog';
import { Label } from '@/Components/ui/label';
import {
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/Components/ui/select';
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
import { LayoutGrid, List, Plus, UserCheck } from 'lucide-react';
import { toast } from 'sonner';

type PipelineCard = {
    id: number;
    stage: string;
    candidate_name: string | null;
    candidate_email: string | null;
    job_title: string | null;
    company_name: string | null;
    updated_at: string | null;
};

export default function Index({
    stages,
    board,
    applications,
    filters,
    jobs,
    candidates,
    flash,
}: PageProps<{
    stages: string[];
    board: Record<string, PipelineCard[]>;
    applications: PipelineCard[];
    filters: { job_id: string };
    jobs: Array<{ id: number; title: string }>;
    candidates: Array<{ id: number; name: string }>;
}>) {
    const { t } = useLanguage();
    const [view, setView] = useState<'board' | 'list'>('board');
    const [open, setOpen] = useState(false);

    useEffect(() => {
        if (flash?.success) toast.success(flash.success);
    }, [flash?.success]);

    const addForm = useForm({
        candidate_id: String(candidates[0]?.id ?? ''),
        job_id: String(jobs[0]?.id ?? ''),
    });

    const applyJobFilter = (jobId: string) => {
        router.get(route('recruitment.pipeline.index'), {
            job_id: jobId === 'all' ? '' : jobId,
        });
    };

    const submitAdd: FormEventHandler = (e) => {
        e.preventDefault();
        addForm.post(route('recruitment.applications.store'), {
            onSuccess: () => {
                setOpen(false);
                addForm.reset();
            },
        });
    };

    const moveStage = (applicationId: number, stage: string) => {
        router.patch(route('recruitment.applications.stage', applicationId), { stage });
    };

    const hire = (applicationId: number) => {
        if (!window.confirm(t('hireCandidate') + '?')) return;
        router.post(route('recruitment.applications.hire', applicationId));
    };

    const renderCard = (card: PipelineCard) => (
        <Card key={card.id} className="mb-2 shadow-sm">
            <CardContent className="space-y-2 p-3">
                <p className="font-medium text-sm">{card.candidate_name}</p>
                <p className="text-xs text-muted-foreground">{card.job_title}</p>
                <p className="text-xs text-muted-foreground">{card.company_name}</p>
                <Select
                    value={card.stage}
                    onValueChange={(v) => moveStage(card.id, v)}
                >
                    <SelectTrigger className="h-8 text-xs">
                        <SelectValue />
                    </SelectTrigger>
                    <SelectContent>
                        {stages.map((s) => (
                            <SelectItem key={s} value={s}>
                                {s}
                            </SelectItem>
                        ))}
                    </SelectContent>
                </Select>
                {card.stage !== 'hired' && card.stage !== 'rejected' && (
                    <Button
                        type="button"
                        size="sm"
                        variant="secondary"
                        className="w-full"
                        onClick={() => hire(card.id)}
                    >
                        <UserCheck className="mr-1 h-3 w-3" />
                        {t('hireCandidate')}
                    </Button>
                )}
            </CardContent>
        </Card>
    );

    return (
        <HrisLayout>
            <Head title={t('pipeline')} />

            <div className="space-y-6">
                <div className="flex flex-wrap items-center justify-between gap-3">
                    <h1 className="text-2xl font-bold text-foreground">{t('pipeline')}</h1>
                    <div className="flex flex-wrap items-center gap-2">
                        <Button
                            type="button"
                            variant={view === 'board' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setView('board')}
                        >
                            <LayoutGrid className="mr-1 h-4 w-4" />
                            {t('boardView')}
                        </Button>
                        <Button
                            type="button"
                            variant={view === 'list' ? 'default' : 'outline'}
                            size="sm"
                            onClick={() => setView('list')}
                        >
                            <List className="mr-1 h-4 w-4" />
                            {t('listView')}
                        </Button>
                        <Dialog open={open} onOpenChange={setOpen}>
                            <DialogTrigger asChild>
                                <Button size="sm">
                                    <Plus className="mr-1 h-4 w-4" />
                                    {t('applyToJob')}
                                </Button>
                            </DialogTrigger>
                            <DialogContent>
                                <DialogHeader>
                                    <DialogTitle>{t('applyToJob')}</DialogTitle>
                                </DialogHeader>
                                <form onSubmit={submitAdd} className="space-y-4">
                                    <div className="space-y-2">
                                        <Label>{t('candidates')}</Label>
                                        <Select
                                            value={addForm.data.candidate_id}
                                            onValueChange={(v) =>
                                                addForm.setData('candidate_id', v)
                                            }
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {candidates.map((c) => (
                                                    <SelectItem
                                                        key={c.id}
                                                        value={String(c.id)}
                                                    >
                                                        {c.name}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <div className="space-y-2">
                                        <Label>{t('jobs')}</Label>
                                        <Select
                                            value={addForm.data.job_id}
                                            onValueChange={(v) => addForm.setData('job_id', v)}
                                        >
                                            <SelectTrigger>
                                                <SelectValue />
                                            </SelectTrigger>
                                            <SelectContent>
                                                {jobs.map((j) => (
                                                    <SelectItem key={j.id} value={String(j.id)}>
                                                        {j.title}
                                                    </SelectItem>
                                                ))}
                                            </SelectContent>
                                        </Select>
                                    </div>
                                    <Button type="submit" disabled={addForm.processing}>
                                        {t('save')}
                                    </Button>
                                </form>
                            </DialogContent>
                        </Dialog>
                    </div>
                </div>

                <Card>
                    <CardContent className="p-4">
                        <Select
                            value={filters.job_id || 'all'}
                            onValueChange={applyJobFilter}
                        >
                            <SelectTrigger className="w-64">
                                <SelectValue placeholder={t('jobs')} />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">{t('all')}</SelectItem>
                                {jobs.map((j) => (
                                    <SelectItem key={j.id} value={String(j.id)}>
                                        {j.title}
                                    </SelectItem>
                                ))}
                            </SelectContent>
                        </Select>
                    </CardContent>
                </Card>

                {view === 'board' ? (
                    <div className="flex gap-3 overflow-x-auto pb-4">
                        {stages.map((stage) => (
                            <div key={stage} className="min-w-[240px] flex-1">
                                <Card>
                                    <CardHeader className="py-3">
                                        <CardTitle className="flex items-center justify-between text-sm">
                                            <span className="capitalize">{stage}</span>
                                            <Badge variant="secondary">
                                                {(board[stage] ?? []).length}
                                            </Badge>
                                        </CardTitle>
                                    </CardHeader>
                                    <CardContent className="max-h-[70vh] overflow-y-auto p-2">
                                        {(board[stage] ?? []).length === 0 ? (
                                            <p className="px-2 py-4 text-center text-xs text-muted-foreground">
                                                {t('noData')}
                                            </p>
                                        ) : (
                                            (board[stage] ?? []).map(renderCard)
                                        )}
                                    </CardContent>
                                </Card>
                            </div>
                        ))}
                    </div>
                ) : (
                    <Card>
                        <CardContent className="p-0">
                            <Table>
                                <TableHeader>
                                    <TableRow>
                                        <TableHead>{t('candidates')}</TableHead>
                                        <TableHead>{t('jobs')}</TableHead>
                                        <TableHead>{t('companies')}</TableHead>
                                        <TableHead>{t('status')}</TableHead>
                                        <TableHead />
                                    </TableRow>
                                </TableHeader>
                                <TableBody>
                                    {applications.length === 0 ? (
                                        <TableRow>
                                            <TableCell
                                                colSpan={5}
                                                className="py-8 text-center text-muted-foreground"
                                            >
                                                {t('noData')}
                                            </TableCell>
                                        </TableRow>
                                    ) : (
                                        applications.map((row) => (
                                            <TableRow key={row.id}>
                                                <TableCell>{row.candidate_name}</TableCell>
                                                <TableCell>{row.job_title}</TableCell>
                                                <TableCell>{row.company_name}</TableCell>
                                                <TableCell>
                                                    <Select
                                                        value={row.stage}
                                                        onValueChange={(v) =>
                                                            moveStage(row.id, v)
                                                        }
                                                    >
                                                        <SelectTrigger className="h-8 w-36">
                                                            <SelectValue />
                                                        </SelectTrigger>
                                                        <SelectContent>
                                                            {stages.map((s) => (
                                                                <SelectItem key={s} value={s}>
                                                                    {s}
                                                                </SelectItem>
                                                            ))}
                                                        </SelectContent>
                                                    </Select>
                                                </TableCell>
                                                <TableCell>
                                                    {row.stage !== 'hired' &&
                                                        row.stage !== 'rejected' && (
                                                            <Button
                                                                type="button"
                                                                size="sm"
                                                                variant="outline"
                                                                onClick={() => hire(row.id)}
                                                            >
                                                                {t('hireCandidate')}
                                                            </Button>
                                                        )}
                                                </TableCell>
                                            </TableRow>
                                        ))
                                    )}
                                </TableBody>
                            </Table>
                        </CardContent>
                    </Card>
                )}
            </div>
        </HrisLayout>
    );
}
