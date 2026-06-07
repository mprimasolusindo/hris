import { Badge } from '@/Components/ui/badge';

const variants: Record<string, 'default' | 'secondary' | 'destructive'> = {
    active: 'default',
    resigned: 'secondary',
    terminated: 'destructive',
    retired: 'secondary',
    suspended: 'destructive',
};

export function EmployeeStatusBadge({ status }: { status: string }) {
    return (
        <Badge variant={variants[status] || 'default'}>{status}</Badge>
    );
}
