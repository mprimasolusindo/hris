import HrisLayout from '@/Layouts/HrisLayout';
import { Card, CardContent } from '@/Components/ui/card';
import { PageProps } from '@/types';
import { Head } from '@inertiajs/react';
import DeleteUserForm from './Partials/DeleteUserForm';
import UpdatePasswordForm from './Partials/UpdatePasswordForm';
import UpdateProfileInformationForm from './Partials/UpdateProfileInformationForm';

export default function Edit({
    mustVerifyEmail,
    status,
}: PageProps<{ mustVerifyEmail: boolean; status?: string }>) {
    return (
        <HrisLayout>
            <Head title="Profile" />

            <div className="mx-auto max-w-3xl space-y-6">
                <h1 className="text-2xl font-bold text-foreground">Profile</h1>

                <Card className="shadow-sm">
                    <CardContent className="p-6">
                        <UpdateProfileInformationForm
                            mustVerifyEmail={mustVerifyEmail}
                            status={status}
                            className="max-w-xl"
                        />
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardContent className="p-6">
                        <UpdatePasswordForm className="max-w-xl" />
                    </CardContent>
                </Card>

                <Card className="shadow-sm">
                    <CardContent className="p-6">
                        <DeleteUserForm className="max-w-xl" />
                    </CardContent>
                </Card>
            </div>
        </HrisLayout>
    );
}
