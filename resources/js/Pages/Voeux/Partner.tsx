import { Head } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';

// Page affichant les vœux du partenaire (sera complétée en Task 8)
export default function Partner({
    generated_text,
    partner_name,
}: {
    generated_text: string;
    partner_name: string;
}) {
    return (
        <AuthenticatedLayout>
            <Head title={`Vœux de ${partner_name}`} />
            <div className="py-12">
                <div className="mx-auto max-w-3xl px-4">
                    <h1 className="mb-6 text-2xl font-bold">Vœux de {partner_name}</h1>
                    <div className="whitespace-pre-wrap rounded-lg bg-white p-6 shadow">
                        {generated_text}
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
