import { usePage } from '@inertiajs/react';
import { PageProps } from '@/types';

export function useCan() {
    const { auth } = usePage<PageProps>().props;
    const permissions = auth.permissions ?? [];

    const can = (permission: string): boolean => {
        if (permissions.includes(permission)) {
            return true;
        }

        const [module] = permission.split('.');
        if (permissions.includes(`${module}.*`)) {
            return true;
        }

        return false;
    };

    const canAny = (keys: string[]): boolean => keys.some((key) => can(key));

    const canAll = (keys: string[]): boolean => keys.every((key) => can(key));

    return { can, canAny, canAll, permissions, roles: auth.roles ?? [] };
}
