import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link } from '@inertiajs/react';
import { PropsWithChildren } from 'react';

export default function Guest({ children }: PropsWithChildren) {
    return (
        <div className="relative flex min-h-screen flex-col items-center px-4 pt-6 sm:justify-center sm:pt-0">
            <div className="app-atmosphere" aria-hidden />
            <div className="relative z-10">
                <Link href="/">
                    <ApplicationLogo className="h-20 w-20 fill-current text-muted-foreground" />
                </Link>
            </div>

            <div className="glass-panel relative z-10 mt-6 w-full overflow-hidden px-6 py-4 sm:max-w-md sm:rounded-lg">
                {children}
            </div>
        </div>
    );
}
