import { Button } from '@/Components/ui/button';
import type { Paginated } from '@/types/pagination';
import { Link } from '@inertiajs/react';

type TablePaginationProps<T> = {
    paginator: Paginated<T>;
};

export function TablePagination<T>({ paginator }: TablePaginationProps<T>) {
    if (paginator.last_page <= 1) {
        return null;
    }

    return (
        <div className="flex flex-wrap gap-2">
            {paginator.links.map((link, i) =>
                link.url ? (
                    <Button
                        key={i}
                        variant={link.active ? 'default' : 'outline'}
                        size="sm"
                        asChild
                    >
                        <Link href={link.url} preserveScroll>
                            <span
                                dangerouslySetInnerHTML={{
                                    __html: link.label,
                                }}
                            />
                        </Link>
                    </Button>
                ) : null,
            )}
        </div>
    );
}
