import HrisLayout from '@/Layouts/HrisLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useLanguage } from '@/i18n/LanguageContext';
import { Card, CardContent, CardHeader, CardTitle } from '@/Components/ui/card';
import { Input } from '@/Components/ui/input';
import { PageProps } from '@/types';
import { Search as SearchIcon, Users, Briefcase, UserPlus } from 'lucide-react';
import { FormEventHandler, useState } from 'react';

type ResultItem = {
    id: number;
    title: string;
    subtitle: string | null;
    meta: string | null;
    url: string;
};

type Results = {
    employees: ResultItem[];
    jobs: ResultItem[];
    candidates: ResultItem[];
};

export default function Index({
    query,
    results,
    total,
}: PageProps<{ query: string; results: Results; total: number }>) {
    const { t } = useLanguage();
    const [term, setTerm] = useState(query);

    const submit: FormEventHandler = (e) => {
        e.preventDefault();
        router.get(route('search.index'), { q: term });
    };

    const sections: Array<{
        key: keyof Results;
        label: string;
        icon: typeof Users;
    }> = [
        { key: 'employees', label: t('employees'), icon: Users },
        { key: 'jobs', label: t('jobs'), icon: Briefcase },
        { key: 'candidates', label: t('candidates'), icon: UserPlus },
    ];

    return (
        <HrisLayout>
            <Head title={t('searchResults')} />

            <div className="space-y-6">
                <h1 className="text-2xl font-bold text-foreground">
                    {t('searchResults')}
                </h1>

                <form onSubmit={submit} className="relative max-w-xl">
                    <SearchIcon className="absolute left-2.5 top-2.5 h-4 w-4 text-muted-foreground" />
                    <Input
                        autoFocus
                        placeholder={t('searchEverything')}
                        className="pl-8"
                        value={term}
                        onChange={(e) => setTerm(e.target.value)}
                    />
                </form>

                {query !== '' && (
                    <p className="text-sm text-muted-foreground">
                        {t('resultsFor')}: <strong>{query}</strong> ({total})
                    </p>
                )}

                {query !== '' && total === 0 ? (
                    <Card>
                        <CardContent className="py-10 text-center text-muted-foreground">
                            {t('noResults')}
                        </CardContent>
                    </Card>
                ) : (
                    sections.map((section) => {
                        const items = results[section.key];
                        if (items.length === 0) return null;
                        const Icon = section.icon;

                        return (
                            <Card key={section.key}>
                                <CardHeader>
                                    <CardTitle className="flex items-center gap-2 text-base">
                                        <Icon className="h-4 w-4" />
                                        {section.label} ({items.length})
                                    </CardTitle>
                                </CardHeader>
                                <CardContent className="divide-y">
                                    {items.map((item) => (
                                        <Link
                                            key={item.id}
                                            href={item.url}
                                            className="flex items-center justify-between py-2.5 hover:bg-muted/50"
                                        >
                                            <div>
                                                <p className="font-medium text-foreground">
                                                    {item.title}
                                                </p>
                                                {item.subtitle && (
                                                    <p className="text-xs text-muted-foreground">
                                                        {item.subtitle}
                                                    </p>
                                                )}
                                            </div>
                                            {item.meta && (
                                                <span className="text-xs text-muted-foreground">
                                                    {item.meta}
                                                </span>
                                            )}
                                        </Link>
                                    ))}
                                </CardContent>
                            </Card>
                        );
                    })
                )}
            </div>
        </HrisLayout>
    );
}
