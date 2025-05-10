import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';

export default function Dashboard({ auth }) {
    return (
        <AuthenticatedLayout
            user={auth.user}
        >
            <Head title="Dashboard" />
            {/* Este container define a área principal abaixo do header do AuthenticatedLayout.
                A altura é calculada para preencher o restante da viewport.
                Ajustado para considerar principalmente a nav superior (~64px) para aumentar a altura. */}
            <div style={{ height: 'calc(100vh - 64px)' }}>
                {/* O card que contém o iframe. Ele ocupará toda a altura e largura do seu pai.
                    Classes como shadow-sm e sm:rounded-lg foram removidas para um visual mais "colado",
                    mas podem ser adicionadas de volta se preferir um estilo de card. */}
                <div className="bg-white dark:bg-gray-800 h-full w-full overflow-hidden">
                    {/* Wrapper do iframe, ocupando toda a altura do card */}
                    <div className="h-full">
                        <iframe
                            src="http://127.0.0.1:3000/"
                            title="Frontend Dashboard"
                            style={{ width: '100%', height: '100%', border: 'none' }}
                        />
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
