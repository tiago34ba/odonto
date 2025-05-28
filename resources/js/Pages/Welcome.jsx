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
                        <Link href="/" className="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors">
                            {/* Logo Genérica de Tecnologia */}
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-8 w-8 text-blue-600" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <rect x="7" y="7" width="10" height="10" rx="2" strokeWidth="2" />
                                <path strokeWidth="2" d="M11 11h2v2h-2z" />
                                <path strokeWidth="2" d="M4 8V4m0 0h4M4 4l4 4M20 8V4m0 0h-4m4 0l-4 4M4 16v4m0 0h4m-4 0l4-4M20 16v4m0 0h-4m4 0l-4-4" />
                            </svg>
                            <span className="hidden sm:inline">Ssait Odonto</span>
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

                                {/* Quem somos */}
                                <section className="mt-20 mb-20 bg-gray-100 dark:bg-gray-900 rounded-2xl p-8 shadow-lg max-w-5xl mx-auto">
                                    <h2 className="text-4xl font-extrabold text-blue-700 mb-6 text-center">
                                        Quem somos
                                    </h2>
                                    <div className="flex flex-col md:flex-row gap-8 items-center">
                                        {/* Imagem única de clínica */}
                                        <div className="flex-1 flex flex-col gap-4">
                                            <img
                                                src="https://images.pexels.com/photos/3845810/pexels-photo-3845810.jpeg?auto=compress&w=800&q=80"
                                                alt="Foto de um consultório odontológico"
                                                className="rounded-2xl object-cover w-full h-96"
                                            />
                                        </div>
                                        {/* Texto */}
                                        <div className="flex-1">
                                            <h3 className="text-2xl font-bold text-gray-900 dark:text-white mb-2 flex items-center gap-2">
                                                Nossa clínica
                                                <span className="text-yellow-500 text-base font-semibold flex items-center gap-1">
                                                    <i className="fas fa-info-circle"></i> Clínica particular
                                                </span>
                                            </h3>
                                            <p className="text-gray-700 dark:text-gray-200 mb-4">
                                                A Implante Dentario, com +5 anos de experiência e +20 mil pacientes satisfeitos, foca na odontologia sem dor e humanizado.
                                            </p>
                                            <p className="font-semibold text-gray-800 dark:text-gray-100 mb-2">
                                                O que diferencia a Implante Master das outras clínicas:
                                            </p>
                                            <ul className="space-y-2">
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">Laboratório odontológico próprio:</span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Confecção das próteses sobre implantes, o que nos permite oferecer uma garantia de qualidade e velocidade.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">Terapia nutricional e aplicação de laser pós-cirúrgico:</span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Visa fornecer orientação dietética adequada para otimizar a recuperação e a cicatrização após a intervenção.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">Suporte médico para cirurgias, anestesista e ortopedista:</span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Assegurando o conforto e segurança do paciente durante a intervenção.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">Bomba infusora de anestésico:</span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Proporcionando anestesia sem dor e um procedimento mais leve.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">Scanner intra oral:</span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Proporciona melhor visualização da cavidade oral e assertividade nos procedimentos.
                                                        </span>
                                                    </span>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </section>
                                {/* Fim Quem somos */}

                                {/* Nossa equipe de Especialistas */}
                                <section className="mb-20 max-w-7xl mx-auto">
                                    <h2 className="text-4xl font-extrabold text-blue-900 mb-10 text-center">
                                        Nossa equipe de <span className="text-yellow-400">Especialistas</span>
                                    </h2>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                                        {/* Especialista 1 */}
                                        <div className="bg-blue-100 rounded-2xl p-6 flex flex-col items-center shadow-md">
                                            <img
                                                src="https://randomuser.me/api/portraits/men/32.jpg"
                                                alt="Dr. Nicolas Nelli"
                                                className="rounded-xl w-40 h-40 object-cover mb-4"
                                            />
                                            <h3 className="text-xl font-bold text-blue-900 mb-1 text-center">Dr. Nicolas Nelli</h3>
                                            <p className="font-semibold text-blue-800 mb-1 text-center">CRO-BA 21609</p>
                                            <p className="text-gray-700 text-center text-sm">
                                                Seguindo a carreira do pai, Nicolas Nelli atua como cirurgião dentista, especialista em próteses.
                                            </p>
                                        </div>
                                        {/* Especialista 2 */}
                                        <div className="bg-blue-100 rounded-2xl p-6 flex flex-col items-center shadow-md">
                                            <img
                                                src="https://randomuser.me/api/portraits/men/44.jpg"
                                                alt="Dr. Alfredo Nelli"
                                                className="rounded-xl w-40 h-40 object-cover mb-4"
                                            />
                                            <h3 className="text-xl font-bold text-blue-900 mb-1 text-center">Dr. Alfredo Nelli</h3>
                                            <p className="font-semibold text-blue-800 mb-1 text-center">CRO-BA 3336</p>
                                            <p className="text-gray-700 text-center text-sm">
                                                Referência em implantes na Bahia, Alfredo atua há mais de 35 anos na área, unindo experiência e conhecimento como fundador, responsável técnico e líder da equipe multidisciplinar da Implante Master.
                                            </p>
                                        </div>
                                        {/* Especialista 3 */}
                                        <div className="bg-blue-100 rounded-2xl p-6 flex flex-col items-center shadow-md">
                                            <img
                                                src="https://randomuser.me/api/portraits/men/65.jpg"
                                                alt="Dr. Gustavo Nelli"
                                                className="rounded-xl w-40 h-40 object-cover mb-4"
                                            />
                                            <h3 className="text-xl font-bold text-blue-900 mb-1 text-center">Dr. Gustavo Nelli</h3>
                                            <p className="font-semibold text-blue-800 mb-1 text-center">CRO-BA 29965</p>
                                            <p className="text-gray-700 text-center text-sm">
                                                Seguindo a carreira do pai, Gustavo Nelli atua como endodontista, especialista em tratamento de canal.
                                            </p>
                                        </div>
                                        {/* Especialista 4 */}
                                        <div className="bg-blue-100 rounded-2xl p-6 flex flex-col items-center shadow-md">
                                            <img
                                                src="https://randomuser.me/api/portraits/men/51.jpg"
                                                alt="Dr. Igor Brandão"
                                                className="rounded-xl w-40 h-40 object-cover mb-4"
                                            />
                                            <h3 className="text-xl font-bold text-blue-900 mb-1 text-center">Dr. Igor Brandão</h3>
                                            <p className="font-semibold text-blue-800 mb-1 text-center">CRO-BA 20388</p>
                                            <p className="text-gray-700 text-center text-sm">
                                                Dr. Igor Brandão é cirurgião dentista e especialista em reabilitação orofacial.
                                            </p>
                                        </div>
                                    </div>
                                </section>
                                {/* Fim Nossa equipe de Especialistas */}
                            </main>
                        </div>

                        {/* Rodapé customizado */}
                        <footer className="w-full bg-white dark:bg-gray-900 border-t border-gray-200 mt-16">
                            <div className="max-w-7xl mx-auto px-4 py-12 sm:px-6 lg:px-8">
                                <div className="flex flex-col md:flex-row md:justify-between md:items-start gap-8">
                                    {/* Coluna 1 */}
                                    <div className="mb-8 md:mb-0">
                                        <h2 className="text-2xl font-bold text-blue-600 mb-2">Ssait Odonto</h2>
                                        <p className="text-gray-700 dark:text-gray-300 max-w-xs mb-4">
                                            Feito por uma empressa espacializada em criação de sistemas sobe Demanda
                                        </p>
                                        <div className="flex space-x-4 mt-6 md:mt-12">
                                            <a href="#" className="text-blue-600 hover:text-blue-800" aria-label="Facebook">
                                                <i className="fab fa-facebook-f text-2xl"></i>
                                            </a>
                                            <a href="#" className="text-blue-600 hover:text-blue-800" aria-label="Instagram">
                                                <i className="fab fa-instagram text-2xl"></i>
                                            </a>
                                            <a href="#" className="text-blue-600 hover:text-blue-800" aria-label="YouTube">
                                                <i className="fab fa-youtube text-2xl"></i>
                                            </a>
                                            <a href="#" className="text-blue-600 hover:text-blue-800" aria-label="LinkedIn">
                                                <i className="fab fa-linkedin-in text-2xl"></i>
                                            </a>
                                        </div>
                                    </div>
                                    {/* Coluna 2 */}
                                    <div className="grid grid-cols-2 md:grid-cols-4 gap-8 flex-1">
                                        <div>
                                            <h3 className="font-semibold text-blue-600 mb-3">Suporte</h3>
                                            <ul className="space-y-2 text-gray-800 dark:text-gray-200 text-sm">
                                                <li><a href="#" className="hover:text-blue-600">Pedir demonstração</a></li>
                                                <li><a href="#" className="hover:text-blue-600">Tire suas dúvidas</a></li>
                                                <li><a href="#" className="hover:text-blue-600">Central de ajuda</a></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-blue-600 mb-3">Institucional</h3>
                                            <ul className="space-y-2 text-gray-800 dark:text-gray-200 text-sm">
                                                <li><a href="#" className="hover:text-blue-600">Sobre nós</a></li>
                                                <li><a href="#" className="hover:text-blue-600">Blog SSait</a></li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-blue-600 mb-3">Produto</h3>
                                            <ul className="space-y-2 text-gray-800 dark:text-gray-200 text-sm">
                                                <li><a href="#" className="hover:text-blue-600">Planos e preços</a></li>
                                                <li><a href="#" className="hover:text-blue-600">Recursos</a></li>
                                                <li><a href="#" className="hover:text-blue-600">Contrato Odontológico</a></li>
                                                <li><a href="#" className="hover:text-blue-600">Depoimentos</a></li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div className="border-t border-gray-200 mt-12 pt-6 flex flex-col md:flex-row items-center justify-between text-gray-500 text-xs">
                                    <div>
                                        © 2025 SSait Tecnlogia – Todos os direitos reservados
                                        <span className="mx-2">|</span>
                                        <a href="#" className="hover:text-blue-600">Termos de uso</a>
                                        <span className="mx-2">|</span>
                                        <a href="#" className="hover:text-blue-600">Política de Privacidade</a>
                                    </div>
                                    <div className="flex space-x-4 mt-4 md:mt-0">
                                        <a href="#" className="text-blue-600 hover:text-blue-800"><i className="fab fa-facebook-f"></i></a>
                                        <a href="#" className="text-blue-600 hover:text-blue-800"><i className="fab fa-instagram"></i></a>
                                        <a href="#" className="text-blue-600 hover:text-blue-800"><i className="fab fa-youtube"></i></a>
                                        <a href="#" className="text-blue-600 hover:text-blue-800"><i className="fab fa-linkedin-in"></i></a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                        {/* Fim do rodapé customizado */}
                    </div>
                </div>
            </div>
        </>
    );
}
