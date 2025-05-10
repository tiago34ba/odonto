import { Head, Link } from '@inertiajs/react';

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    const handleImageError = () => {
        document
            .getElementById('screenshot-container')
            ?.classList.add('!hidden');
        document.getElementById('docs-card')?.classList.add('!row-span-1');
        document
            .getElementById('docs-card-content')
            ?.classList.add('!flex-row');
        document.getElementById('background')?.classList.add('!hidden');
    };

    return (
        <>
            <Head title="Welcome" />
            {/* Removida a imagem de fundo do Laravel daqui */}
            {/* <img
                    id="background"
                    className="absolute -left-20 top-0 max-w-[877px]"
                    src="https://laravel.com/assets/img/welcome/background.svg"
                /> */}

            {/* Navbar Fixa e Modernizada */}
            <header className="fixed top-0 left-0 right-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md shadow-lg">
                <div className="container mx-auto flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
                    {/* Logo */}
                    <div className="flex-shrink-0">
                        <Link href="/" className="text-2xl font-bold text-gray-800 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            SeuLogo
                        </Link>
                    </div>

                    {/* Menu Principal Centralizado */}
                    <nav className="hidden md:flex flex-grow justify-center space-x-6">
                        {[
                            { href: '#', label: 'Soluções' },
                            { href: '#', label: 'Serviços' },
                            { href: '#', label: 'Planos' },
                            { href: '#', label: 'Blog e Conteúdos' },
                        ].map((item) => (
                            <Link
                                key={item.label}
                                href={item.href}
                                className="rounded-md px-3 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:text-blue-600 dark:hover:text-blue-400 transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            >
                                {item.label}
                            </Link>
                        ))}
                    </nav>

                    {/* Auth Links */}
                    <nav className="flex-shrink-0 flex items-center space-x-4">
                        {auth.user ? (
                            <Link
                                href={route('dashboard')}
                                className="rounded-md px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                            >
                                Dashboard
                            </Link>
                        ) : (
                            <>
                                <Link href={route('login')} className="rounded-md px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500">
                                    Log in
                                </Link>
                                <Link href={route('register')} className="rounded-md bg-blue-600 px-4 py-2 text-base font-medium text-white hover:bg-blue-700 transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800">
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                    {/* TODO: Adicionar botão de menu hamburger para mobile */}
                </div>
            </header>

            <div className="bg-gray-100 dark:bg-gray-900 text-black/80 dark:text-white/80">
                {/* Container principal da tela: altura mínima da tela, coluna flex, itens centralizados horizontalmente */}
                {/* Adicionado pt-20 (altura da navbar) para evitar que o conteúdo fique sob a navbar */}
                <div className="relative flex min-h-screen flex-col items-center selection:bg-blue-500 selection:text-white pt-20">
                    {/* Bloco de conteúdo: ocupa o espaço vertical disponível, coluna flex, largura máxima, centralizado horizontalmente */}
                    <div className="relative flex w-full max-w-4xl flex-grow flex-col px-6 lg:max-w-7xl">
                        {/* Conteúdo interno: cresce para empurrar o rodapé para baixo, centraliza seu próprio conteúdo (Header, Main) verticalmente */}
                        <div className="flex flex-grow flex-col items-center justify-center text-center">
                            <main className="py-12 px-4 sm:px-6 lg:px-8">
                                <h1 className="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                                    <span className="block">Ssait Odonto</span>
                                    <span className="block text-blue-600 dark:text-blue-400">Todos os recursos que sua clínica
                                    precisa em um só lugar</span>
                                </h1>
                                <p className="mt-6 max-w-md mx-auto text-lg text-gray-600 dark:text-gray-300 sm:text-xl md:mt-8 md:max-w-3xl">
                                O software ideal para dentistas: conheça os benefícios para seu consultório odontológico                                </p>
                                <div className="mt-8 sm:mt-10 flex justify-center space-x-4">
                                    <Link
                                        href="#"
                                        className="inline-flex items-center justify-center px-6 py-3 border border-transparent text-base font-medium rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                                    >
                                        Comece Agora
                                    </Link>
                                    <Link
                                        href="#"
                                        className="inline-flex items-center justify-center px-6 py-3 border border-gray-300 dark:border-gray-600 text-base font-medium rounded-md text-blue-700 dark:text-blue-300 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800"
                                    >
                                        Saiba Mais
                                    </Link>
                                </div>

                                {/* Benefícios */}
                                <div className="mt-12 grid gap-8 sm:grid-cols-2 md:grid-cols-3">
                                    {[
                                        {
                                            title: 'Banco Integrado',
                                            description: 'Gerencie todos os pagamentos da clínica e emita boletos com conciliação automática.',
                                            icon: '💰' // Ícone de dinheiro
                                        },
                                        {
                                            title: 'Reagendamentos Automáticos',
                                            description: 'Garanta o retorno dos pacientes com reagendamentos sem esforço manual.',
                                            icon: '📅' // Ícone de calendário
                                        },
                                        {
                                            title: 'Crédito para Pacientes',
                                            description: 'Aumente a taxa de conversão oferecendo opções de pagamento acessíveis.',
                                            icon: '🏥' // Ícone de hospital/clínica
                                        },
                                    ].map((item) => (
                                        <div key={item.title} className="p-6 bg-white dark:bg-gray-800 rounded-xl shadow-xl hover:shadow-2xl transition-shadow duration-300 transform hover:-translate-y-1 flex flex-col items-center text-center">
                                            {/* Ícone acima do título */}
                                            <span className="text-5xl mb-4">{item.icon}</span>
                                            <h3 className="text-xl font-semibold text-gray-900 dark:text-white mb-2">{item.title}</h3>
                                            <p className="text-gray-600 dark:text-gray-300 text-sm">{item.description}</p>
                                        </div>
                                    ))}
                                </div>
                                <div className="mt-12">
                                    <Link
                                        href="#"
                                        className="inline-flex items-center justify-center px-8 py-3 border border-transparent text-lg font-semibold rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 dark:focus:ring-offset-gray-800 transition-transform transform hover:scale-105"
                                    >
                                        Conheça Todas as Vantagens
                                    </Link>
                                </div>
                            </main>
                        </div>

                        {/* Rodapé: na parte inferior do Bloco de conteúdo, texto centralizado */}
                        <footer className="py-12 text-center text-sm text-gray-500 dark:text-gray-400">
                            Laravel v{laravelVersion} (PHP v{phpVersion})
                        </footer>
                    </div>
                </div>
            </div>
        </>
    );
}
