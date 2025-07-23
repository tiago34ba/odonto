import { Head, Link } from '@inertiajs/react';
import { useState } from 'react';

// Componente seguro para lista de funcionalidades
function FuncionalidadesList({ items }) {
    return (
        <ul className="mt-4 mb-2 text-left space-y-2 text-sm">
            {items.map((item, idx) => (
                <li key={idx} className="flex items-start gap-2">
                    <svg
                        className="text-blue-700 mt-0.5 flex-shrink-0"
                        width="28"
                        height="28"
                        viewBox="0 0 28 28"
                        fill="none"
                        aria-hidden="true"
                        focusable="false"
                    >
                        <path
                            d="M7 15L12 20L21 9"
                            stroke="currentColor"
                            strokeWidth="3"
                            strokeLinecap="round"
                            strokeLinejoin="round"
                        />
                    </svg>
                    <span>{item}</span>
                </li>
            ))}
        </ul>
    );
}

// Componente seguro para especialistas
function EspecialistaCard({ nome, img, cro, desc }) {
    return (
        <div className="bg-blue-100 rounded-2xl p-6 flex flex-col items-center shadow-md">
            <img
                src={img}
                alt={nome}
                className="rounded-xl w-40 h-40 object-cover mb-4"
                loading="lazy"
                width={160}
                height={160}
                onError={e => {
                    e.currentTarget.src =
                        'https://via.placeholder.com/160x160?text=Sem+Foto';
                }}
                draggable={false}
            />
            <h3 className="text-xl font-bold text-blue-900 mb-1 text-center">
                {nome}
            </h3>
            <p className="font-semibold text-blue-800 mb-1 text-center">{cro}</p>
            <p className="text-gray-700 text-center text-sm">{desc}</p>
        </div>
    );
}

// Novo componente para funcionalidades com ícone
function FuncionalidadeCard({ icon, title, description }) {
    return (
        <div className="flex flex-col items-center bg-white dark:bg-gray-800 rounded-xl shadow-md p-5 hover:shadow-xl transition mb-4">
            <span className="text-3xl mb-2">{icon}</span>
            <span className="font-semibold text-blue-800 dark:text-blue-300 text-base text-center mb-1">{title}</span>
            {description && (
                <span className="text-gray-600 dark:text-gray-300 text-sm text-center">{description}</span>
            )}
        </div>
    );
}

// Lista de cards extras com ícones para exibir abaixo de "Comece Agora" e "Saiba Mais"
const funcionalidadesExtrasCards = [
    { icon: "📢", title: "Campanhas de marketing automáticas*" },
    { icon: "🛟", title: "Suporte Contínuo" },
    { icon: "🏦", title: "Banco Integrado: Gerencia todos os pagamentos da clínica e emite boletos com conciliação automática." },
    { icon: "🔁", title: "Reagendamentos Automáticos: Garanta o retorno dos pacientes com reagendamentos sem esforço manual." },
    { icon: "💳", title: "Crédito para Pacientes: Aumente a taxa de conversão oferecendo opções de pagamento acessíveis." },
    { icon: "🤖", title: "Inteligência Artificial: Crie suas evoluções por voz (Novo)" },
    { icon: "💬", title: "Integração com WhatsApp Web" },
    { icon: "🤝", title: "Copiloto: Usar dentro do seu WhatsApp Web (Novo)" },
    { icon: "📈", title: "CRM de agendamentos com alerta de pacientes inativos / cancelamentos de consultas e controle de retornos" },
    { icon: "📊", title: "CRM de orçamentos em aberto" },
    { icon: "👩‍⚕️", title: "Faceograma feminino e masculino de HOF" },
    { icon: "🎯", title: "Gerenciador de limites para captar novos pacientes (Novo)" },
    { icon: "✅", title: "Confirmar consultas gratuitamente pelo aplicativo para pacientes" },
    { icon: "📄", title: "Modelo de Contrato" },
    { icon: "🎨", title: "Personalizar contratos" },
    { icon: "✍️", title: "Assinatura eletrônica no contrato*" },
    { icon: "📲", title: "Aplicativo Android e iOS" },
    { icon: "🔒", title: "Controle de permissões: Você define quais usuários do sistema têm acesso a quais áreas, criando logins individuais para cada profissional, seja dentista ou secretário(a)." },
    { icon: "🔗", title: "Integração com WhatsApp: Salvar contatos na lista é coisa do passado! Abra o WhatsApp do seu paciente como um passe de mágica usando nossos botões integrados." },
    { icon: "📂", title: "Prontuário na palma da mão: Veja as informações principais de cada paciente, adicione evoluções, confira pagamentos, históricos de consulta e muito mais!" },
    { icon: "📊", title: "BI para redes odontológicas (Novo)" },
    { icon: "📈", title: "Mais de 30 indicadores com dados centralizados" },
    { icon: "📑", title: "Relatórios integrados por áreas, processos e profissionais" },
    { icon: "🏢", title: "Acesso Multiclínicas (Novo)" },
    { icon: "🏆", title: "Ranking de desempenho das unidades" },
    { icon: "🔄", title: "Compartilhamento de dados simplificado e com segurança" },
    { icon: "🦷", title: "100% integrado ao Simples Dental" },
    { icon: "💬", title: "Integração com Whatsapp Web" },
];

export default function Welcome({ auth, laravelVersion, phpVersion }) {
    const handleImageError = () => {
        document.getElementById('screenshot-container')?.classList.add('!hidden');
        document.getElementById('docs-card')?.classList.add('!row-span-1');
        document.getElementById('docs-card-content')?.classList.add('!flex-row');
        document.getElementById('background')?.classList.add('!hidden');
    };

    // Adicionei o state para alternar entre mensal e anual
    const [isAnnual, setIsAnnual] = useState(false);

    // Estado para destacar o plano selecionado
    const [selectedPlan, setSelectedPlan] = useState('Essencial');

    // Preços base
    const prices = {
        essencial: 168.24,
        plus: 240.33,
        pro: 300.83,
        redes: 360.90,
    };

    // Calcula preço anual com 15% de desconto
    const getPrice = value => {
        if (isAnnual) {
            // 15% de desconto: valor * 0.85
            return (value * 0.85).toFixed(2);
        }
        return value.toFixed(2);
    };

    // Função para calcular o valor "de" (5% maior que o preço atual)
    const getOldPrice = value => (value * 1.05).toFixed(2);

    // Funcionalidades específicas para o Plano Essencial
    const funcionalidadesEssencial = [
        'Usuários e armazenamento de imagens ilimitados',
        'Agenda completa com confirmação de consulta*',
        'Alerta de retorno na agenda',
        'Prontuário digital e emissão de documentos',
        'Receituário impresso e 100% digital',
        'Modelos de Anamnese',
        'Orçamentos em aberto',
        'Odontograma',
        'Site para sua clínica',
    ];

    const funcionalidadesPlus = [
        'Tudo do Plano Essencial + funcionalidades',
        'Análise de Crédito',
        'Emissão de Nota Fiscal de Serviço* (Novo)',
        'Fluxo de caixa e comissionamento',
        'Emissão de boletos bancários',
        'Maquininha de cartão integrada',
        'Conferência de transações',
        'Ortodontia: controle dos retornos para manutenções',
        'Controle de Alinhadores (Novo)',
        'Indicadores de performance',
        'Controle de consultas canceladas',
        'Assinatura Eletrônica para evoluções e anamneses*',
        'Controle de Prótese',
        'Automação para controle de taxas de maquininha',
        'Aplicativo Android e iOS',
    ];

    const funcionalidadesExtras = [
        'Aplicativo para clínica e para paciente',
        'Campanhas de marketing automáticas*',
        'Suporte Contínuo',
        'Banco Integrado: Gerencia todos os pagamentos da clínica e emite boletos com conciliação automática.',
        'Reagendamentos Automáticos: Garanta o retorno dos pacientes com reagendamentos sem esforço manual.',
        'Crédito para Pacientes: Aumente a taxa de conversão oferecendo opções de pagamento acessíveis.',
        'Inteligência Artificial: Crie suas evoluções por voz (Novo)',
        'Integração com WhatsApp Web',
        'Copiloto: Usar dentro do seu WhatsApp Web (Novo)',
        'CRM de agendamentos com alerta de pacientes inativos / cancelamentos de consultas e controle de retornos',
        'CRM de orçamentos em aberto',
        'Faceograma feminino e masculino de HOF',
        'Gerenciador de limites para captar novos pacientes (Novo)',
        'Confirmar consultas gratuitamente pelo aplicativo para pacientes',
        'Modelo de Contrato',
        'Personalizar contratos',
        'Assinatura eletrônica no contrato*',
        'Aplicativo Android e iOS',
        'Controle de permissões: Você define quais usuários do sistema têm acesso a quais áreas, criando logins individuais para cada profissional, seja dentista ou secretário(a).',
        'Integração com WhatsApp: Salvar contatos na lista é coisa do passado! Abra o WhatsApp do seu paciente como um passe de mágica usando nossos botões integrados.',
        'Prontuário na palma da mão: Veja as informações principais de cada paciente, adicione evoluções, confira pagamentos, históricos de consulta e muito mais!',
    ];

    const funcionalidadesPro = [
        ...funcionalidadesExtras,
        // (adicione aqui as funcionalidades que já estavam no Pro, se houver)
    ];

    const funcionalidadesRedes = [
        ...funcionalidadesExtras,
        'BI para redes odontológicas (Novo)',
        'Mais de 30 indicadores com dados centralizados',
        'Relatórios integrados por áreas, processos e profissionais',
        'Acesso Multiclínicas (Novo)',
        'Ranking de performance das unidades',
        'Compartilhamento de dados simplificado e com segurança',
        '100% integrado ao Simples Dental',
        'Integração com Whatsapp Web',
        'Aplicativo Android e iOS',
    ];

    const especialistas = [
        {
            nome: 'Dr. Nicolas Nelli',
            img: 'https://randomuser.me/api/portraits/men/32.jpg',
            cro: 'CRO-BA 21609',
            desc: 'Seguindo a carreira do pai, Nicolas Nelli atua como cirurgião dentista, especialista em próteses.',
        },
        {
            nome: 'Dr. Alfredo Nelli',
            img: 'https://randomuser.me/api/portraits/men/44.jpg',
            cro: 'CRO-BA 3336',
            desc: 'Referência em implantes na Bahia, Alfredo atua há mais de 35 anos na área, unindo experiência e conhecimento como fundador, responsável técnico e líder da equipe multidisciplinar da Implante Master.',
        },
        {
            nome: 'Dr. Gustavo Nelli',
            img: 'https://randomuser.me/api/portraits/men/65.jpg',
            cro: 'CRO-BA 29965',
            desc: 'Seguindo a carreira do pai, Gustavo Nelli atua como endodontista, especialista em tratamento de canal.',
        },
        {
            nome: 'Dr. Igor Brandão',
            img: 'https://randomuser.me/api/portraits/men/51.jpg',
            cro: 'CRO-BA 20388',
            desc: 'Dr. Igor Brandão é cirurgião dentista e especialista em reabilitação orofacial.',
        },
    ];

    // Todos os links externos abrem em nova aba e usam rel="noopener noreferrer"
    const safeLinkProps = {
        target: '_blank',
        rel: 'noopener noreferrer',
    };

    // Substitua a lista funcionalidadesEssencial por cards com ícones:
    // Lista de funcionalidades com ícones
    const funcionalidadesCards = [
        { icon: "👥", title: "Usuários e armazenamento de imagens ilimitados" },
        { icon: "📅", title: "Agenda completa com confirmação de consulta*" },
        { icon: "⏰", title: "Alerta de retorno na agenda" },
        { icon: "📝", title: "Prontuário digital e emissão de documentos" },
        { icon: "💊", title: "Receituário impresso e 100% digital" },
        { icon: "📋", title: "Modelos de Anamnese" },
        { icon: "💰", title: "Orçamentos em aberto" },
        { icon: "🦷", title: "Odontograma" },
        { icon: "🌐", title: "Site para sua clínica" },
        { icon: "📱", title: "Aplicativo para a clínica e para o paciente" },
        { icon: "📢", title: "Campanhas de marketing automáticas*" },
        { icon: "🛟", title: "Suporte Contínuo" },
        { icon: "🏦", title: "Banco Integrado: Gerencie todos os pagamentos da clínica e emita boletos com conciliação automática." },
        { icon: "🔁", title: "Reagendamentos Automáticos: Garanta o retorno dos pacientes com reagendamentos sem esforço manual." },
        { icon: "💳", title: "Crédito para Pacientes: Aumente a taxa de conversão oferecendo opções de pagamento acessíveis." },
        { icon: "🤖", title: "Inteligência Artificial: Crie suas evoluções por voz (Novo)" },
        { icon: "💬", title: "Integração com WhatsApp Web" },
        { icon: "🤝", title: "Copiloto: Usar dentro do seu WhatsApp Web (Novo)" },
        { icon: "📈", title: "CRM de agendamentos com alerta de pacientes inativos / cancelamentos de consultas e controle de retornos" },
        { icon: "📊", title: "CRM de orçamentos em aberto" },
        { icon: "👩‍⚕️", title: "Faceograma feminino e masculino de HOF" },
        { icon: "🎯", title: "Gerenciador de indicações para captar novos pacientes (Novo)" },
        { icon: "✅", title: "Confirmar consultas gratuitamente pelo app para pacientes" },
        { icon: "📄", title: "Modelo de Contrato" },
        { icon: "🎨", title: "Personalizar contratos" },
        { icon: "✍️", title: "Assinatura eletrônica em contrato*" },
        { icon: "📲", title: "Aplicativo Android e iOS" },
        { icon: "🔒", title: "Controle de permissões: Você define quais usuários do sistema têm acesso a quais áreas, criando logins individuais para cada profissional, seja dentista ou secretário(a)." },
        { icon: "🔗", title: "Integração com WhatsApp: Salvar contatos na lista é coisa do passado! Abra o WhatsApp do seu paciente como num passe de mágica usando nossos botões integrados." },
        { icon: "📂", title: "Prontuário na palma da mão: Veja as informações principais de cada paciente, adicione evoluções, confira pagamentos, históricos de consulta e muito mais!" },
    ];

    return (
        <>
            <Head title="Welcome" />
            {/* Navbar Fixa e Modernizada */}
            <header className="fixed top-0 left-0 right-0 z-50 bg-white/90 dark:bg-gray-900/90 backdrop-blur-md shadow-lg">
                <div className="container mx-auto flex h-20 items-center justify-between px-4 sm:px-6 lg:px-8">
                    {/* Logo */}
                    <div className="flex-shrink-0">
                        <Link
                            href="/"
                            className="flex items-center gap-2 text-2xl font-bold text-gray-800 dark:text-white hover:text-blue-600 dark:hover:text-blue-400 transition-colors"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                className="h-8 w-8 text-blue-600"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                            >
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
                            { href: '#planos', label: 'Planos' },
                            { href: '#', label: 'Blog e Conteúdos' },
                        ].map(item => (
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
                                <Link
                                    href={route('login')}
                                    className="rounded-md px-4 py-2 text-base font-medium text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500"
                                >
                                    Log in
                                </Link>
                                <Link
                                    href={route('register')}
                                    className="rounded-md bg-blue-600 px-4 py-2 text-base font-medium text-white hover:bg-blue-700 transition-colors duration-300 focus:outline-none focus-visible:ring-2 focus-visible:ring-blue-500 focus-visible:ring-offset-2 dark:focus-visible:ring-offset-gray-800"
                                >
                                    Register
                                </Link>
                            </>
                        )}
                    </nav>
                </div>
            </header>

            <div className="bg-gray-100 dark:bg-gray-900 text-black/80 dark:text-white/80">
                <div className="relative flex min-h-screen flex-col items-center selection:bg-blue-500 selection:text-white pt-20">
                    <div className="relative flex w-full max-w-4xl flex-grow flex-col px-6 lg:max-w-7xl">
                        <div className="flex flex-grow flex-col items-center justify-center text-center">
                            <main className="py-12 px-4 sm:px-6 lg:px-8">
                                <h1 className="text-4xl font-extrabold tracking-tight text-gray-900 dark:text-white sm:text-5xl md:text-6xl">
                                    <span className="block">Ssait Odonto</span>
                                    <span className="block text-blue-600 dark:text-blue-400">
                                        Todos os recursos que sua clínica precisa em um só lugar
                                    </span>
                                </h1>
                                <p className="mt-6 max-w-md mx-auto text-lg text-gray-600 dark:text-gray-300 sm:text-xl md:mt-8 md:max-w-3xl">
                                    O software ideal para dentistas: conheça os benefícios para seu consultório odontológico
                                </p>
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

                                {/* Funcionalidades Extras (agora chamada de Funcionalidades) */}
                                <div className="mt-16 mb-12 max-w-5xl mx-auto">
                                    <h2 className="text-3xl font-extrabold text-blue-700 mb-8 text-center">
                                        Funcionalidades
                                    </h2>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 gap-8">
                                        {funcionalidadesCards.map((item, idx) => (
                                            <FuncionalidadeCard
                                                key={idx}
                                                icon={item.icon}
                                                title={item.title}
                                            />
                                        ))}
                                    </div>
                                </div>

                                {/* Quem somos */}
                                <section className="mt-20 mb-20 bg-gray-100 dark:bg-gray-900 rounded-2xl p-8 shadow-lg max-w-5xl mx-auto">
                                    <h2 className="text-4xl font-extrabold text-blue-700 mb-6 text-center">Quem somos</h2>
                                    <div className="flex flex-col md:flex-row gap-8 items-center">
                                        <div className="flex-1 flex flex-col gap-4">
                                            <img
                                                src="https://images.pexels.com/photos/3845810/pexels-photo-3845810.jpeg?auto=compress&w=800&q=80"
                                                alt="Foto de um consultório odontológico"
                                                className="rounded-2xl object-cover w-full h-96"
                                            />
                                        </div>
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
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">
                                                            Laboratório odontológico próprio:
                                                        </span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Confecção das próteses sobre implantes, o que nos permite oferecer uma garantia de qualidade e velocidade.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">
                                                            Terapia nutricional e aplicação de laser pós-cirúrgico:
                                                        </span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Visa fornecer orientação dietética adequada para otimizar a recuperação e a cicatrização após a intervenção.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">
                                                            Suporte médico para cirurgias, anestesista e ortopedista:
                                                        </span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Assegurando o conforto e segurança do paciente durante a intervenção.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">
                                                            Bomba infusora de anestésico:
                                                        </span>
                                                        <br />
                                                        <span className="text-gray-700 dark:text-gray-200">
                                                            Proporcionando anestesia sem dor e um procedimento mais leve.
                                                        </span>
                                                    </span>
                                                </li>
                                                <li className="flex items-start gap-2">
                                                    <span className="text-blue-700 text-lg mt-1">✔</span>
                                                    <span>
                                                        <span className="font-bold text-blue-900 dark:text-blue-400">
                                                            Scanner intra oral:
                                                        </span>
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

                                {/* Nossa equipe de Especialistas */}
                                <section className="mb-20 max-w-7xl mx-auto">
                                    <h2 className="text-4xl font-extrabold text-blue-900 mb-10 text-center">
                                        Nossa equipe de <span className="text-yellow-400">Especialistas</span>
                                    </h2>
                                    <div className="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-8">
                                        {especialistas.map(esp => (
                                            <EspecialistaCard key={esp.nome} {...esp} />
                                        ))}
                                    </div>
                                </section>

                                {/* Seção de Preços Moderna */}
                                <section id="planos" className="mb-24 max-w-7xl mx-auto px-4">
                                    <h1 className="text-5xl font-extrabold text-blue-700 mb-2 text-center">
                                        Planos e Preços
                                    </h1>
                                    <h2 className="text-3xl md:text-4xl font-extrabold text-blue-900 mb-8 text-center">
                                        Encontre o plano ideal para sua clínica crescer no Plano Essencial
                                    </h2>
                                    <div className="flex justify-center items-center gap-0 mb-10">
                                        <button
                                            type="button"
                                            className={`px-6 py-2 rounded-l-lg font-semibold transition-colors focus:outline-none text-base md:text-lg shadow-sm ${
                                                !isAnnual
                                                    ? 'bg-blue-700 text-white'
                                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200'
                                            }`}
                                            onClick={() => setIsAnnual(false)}
                                        >
                                            Pagamento Mensal
                                        </button>
                                        <button
                                            type="button"
                                            className={`px-6 py-2 rounded-r-lg font-semibold transition-colors focus:outline-none text-base md:text-lg shadow-sm ${
                                                isAnnual
                                                    ? 'bg-blue-700 text-white'
                                                    : 'bg-gray-200 dark:bg-gray-700 text-gray-700 dark:text-gray-200'
                                            }`}
                                            onClick={() => setIsAnnual(true)}
                                        >
                                            Pagamento Anual{' '}
                                            <span className="text-xs font-bold">(mais econômico)</span>
                                        </button>
                                    </div>
                                    <div className="relative grid grid-cols-1 md:grid-cols-4 gap-8 mt-12">
                                        {/* Essencial */}
                                        <div
                                            className={`bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-8 flex flex-col items-center border-2 transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer ${
                                                selectedPlan === 'Essencial'
                                                    ? 'border-blue-700 ring-4 ring-blue-200 scale-105 z-10'
                                                    : 'border-blue-100 hover:border-blue-400'
                                            }`}
                                            onClick={() => setSelectedPlan('Essencial')}
                                        >
                                            <span className="flex items-center gap-2 text-xl font-bold text-blue-700 mb-2 tracking-wide">
                                                <span role="img" aria-label="Essencial">🦷</span>
                                                Essencial
                                            </span>
                                            <p className="text-gray-600 dark:text-gray-300 mb-4 text-center text-sm">
                                                Organize sua rotina com ferramentas práticas e fáceis de usar
                                            </p>
                                            <span className="line-through text-gray-400 text-xs mb-1">
                                                de R$ {getOldPrice(prices.essencial)} por
                                            </span>
                                            <span className="text-4xl font-extrabold text-blue-900 mb-1">
                                                R$ {getPrice(prices.essencial)}
                                                <span className="text-base font-medium text-gray-600">/mês</span>
                                            </span>
                                            <FuncionalidadesList items={funcionalidadesEssencial} />
                                            <button className="mt-4 w-full border-2 border-blue-700 text-blue-700 font-bold rounded-lg py-2 hover:bg-blue-50 transition">
                                                Testar por 7 dias grátis
                                            </button>
                                            <span className="text-xs text-gray-500 mt-2">
                                                Não é necessário cartão de crédito
                                            </span>
                                            {isAnnual && (
                                                <span className="mt-2 text-xs text-green-700 font-semibold">
                                                    15% de desconto aplicado!
                                                </span>
                                            )}
                                        </div>
                                        {/* Plus */}
                                        <div
                                            className={`bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-8 flex flex-col items-center border-2 transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer ${
                                                selectedPlan === 'Plus'
                                                    ? 'border-blue-700 ring-4 ring-blue-200 scale-105 z-10'
                                                    : 'border-blue-100 hover:border-blue-400'
                                            }`}
                                            onClick={() => setSelectedPlan('Plus')}
                                        >
                                            <span className="flex items-center gap-2 text-xl font-bold text-blue-700 mb-2 tracking-wide">
                                                <span role="img" aria-label="Plus">✨</span>
                                                Plus
                                            </span>
                                            <p className="text-gray-600 dark:text-gray-300 mb-4 text-center text-sm">
                                                Expanda sua clínica e otimize sua gestão e o trabalho da equipe
                                            </p>
                                            <span className="line-through text-gray-400 text-xs mb-1">
                                                de R$ {getOldPrice(prices.plus)} por
                                            </span>
                                            <span className="text-4xl font-extrabold text-blue-900 mb-1">
                                                R$ {getPrice(prices.plus)}
                                                <span className="text-base font-medium text-gray-600">/mês</span>
                                            </span>
                                            <FuncionalidadesList items={funcionalidadesPlus} />
                                            <button className="mt-4 w-full border-2 border-blue-700 text-blue-700 font-bold rounded-lg py-2 hover:bg-blue-50 transition">
                                                Testar por 7 dias grátis
                                            </button>
                                            <span className="text-xs text-gray-500 mt-2">
                                                Não é necessário cartão de crédito
                                            </span>
                                            {isAnnual && (
                                                <span className="mt-2 text-xs text-green-700 font-semibold">
                                                    15% de desconto aplicado!
                                                </span>
                                            )}
                                        </div>
                                        {/* Pro - Mais Popular */}
                                        <div
                                            className={`bg-gradient-to-br from-blue-700 to-blue-900 text-white rounded-3xl shadow-2xl p-10 flex flex-col items-center border-4 relative transition-all duration-300 hover:scale-110 hover:shadow-3xl mt-8 md:mt-0 cursor-pointer ${
                                                selectedPlan === 'Pro'
                                                    ? 'border-orange-500 ring-4 ring-orange-200 scale-110 z-20'
                                                    : 'border-orange-400'
                                            }`}
                                            onClick={() => setSelectedPlan('Pro')}
                                            style={{ top: '-2rem' }}
                                        >
                                            <span
                                                className="mb-3 inline-block bg-orange-500 text-white text-xs font-bold px-4 py-1 rounded-full shadow-lg absolute -top-6 left-1/2 -translate-x-1/2"
                                                style={{ zIndex: 2 }}
                                            >
                                                MAIS POPULAR
                                            </span>
                                            <span className="flex items-center gap-2 text-xl font-bold mb-2 tracking-wide">
                                                <span role="img" aria-label="Pro">🚀</span>
                                                Pro
                                            </span>
                                            <p className="mb-4 text-center text-sm">
                                                Eleve sua clínica ao nível máximo de eficiência e agilidade
                                            </p>
                                            <span className="line-through text-blue-200 text-xs mb-1">
                                                de R$ {getOldPrice(prices.pro)} por
                                            </span>
                                            <span className="text-4xl font-extrabold mb-1">
                                                R$ {getPrice(prices.pro)}
                                                <span className="text-base font-medium">/mês</span>
                                            </span>
                                            <FuncionalidadesList items={funcionalidadesPro} />
                                            <button className="mt-4 w-full bg-orange-500 hover:bg-orange-600 text-white font-bold rounded-lg py-2 transition">
                                                Testar por 7 dias grátis
                                            </button>
                                            <span className="text-xs text-blue-100 mt-2">
                                                Não é necessário cartão de crédito
                                            </span>
                                            {isAnnual && (
                                                <span className="mt-2 text-xs text-green-200 font-semibold">
                                                    15% de desconto aplicado!
                                                </span>
                                            )}
                                        </div>
                                        {/* Redes e Franquias - Novidade */}
                                        <div
                                            className={`bg-white dark:bg-gray-800 rounded-3xl shadow-xl p-8 flex flex-col items-center border-2 relative transition-all duration-300 hover:scale-105 hover:shadow-2xl cursor-pointer ${
                                                selectedPlan === 'Redes e Franquias'
                                                    ? 'border-blue-700 ring-4 ring-blue-200 scale-105 z-10'
                                                    : 'border-blue-100 hover:border-blue-400'
                                            }`}
                                            onClick={() => setSelectedPlan('Redes e Franquias')}
                                        >
                                            <span className="absolute -top-5 left-1/2 -translate-x-1/2 bg-blue-200 text-blue-800 text-xs font-bold px-4 py-1 rounded-full shadow-lg">
                                                NOVIDADE
                                            </span>
                                            <span className="flex items-center gap-2 text-xl font-bold text-blue-700 mb-2 tracking-wide">
                                                <span role="img" aria-label="Redes e Franquias">🏢</span>
                                                Redes e Franquias
                                            </span>
                                            <p className="text-gray-600 dark:text-gray-300 mb-4 text-center text-sm">
                                                Para negócios em rede ou franquia e descubra oportunidades para crescer
                                            </p>
                                            <span className="line-through text-gray-400 text-xs mb-1">
                                                de R$ {getOldPrice(prices.redes)} por
                                            </span>
                                            <span className="text-4xl font-extrabold text-blue-900 mb-1">
                                                R$ {getPrice(prices.redes)}
                                                <span className="text-base font-medium text-gray-600">/mês</span>
                                            </span>
                                            <FuncionalidadesList items={funcionalidadesRedes} />
                                            <button className="mt-4 w-full border-2 border-blue-700 text-blue-700 font-bold rounded-lg py-2 hover:bg-blue-50 transition">
                                                Testar por 7 dias grátis
                                            </button>
                                            <span className="text-xs text-gray-500 mt-2">
                                                Não é necessário cartão de crédito
                                            </span>
                                            {isAnnual && (
                                                <span className="mt-2 text-xs text-green-700 font-semibold">
                                                    15% de desconto aplicado!
                                                </span>
                                            )}
                                        </div>
                                    </div>
                                    <div className="flex justify-center mt-10">
                                        <span className="text-xs text-gray-400 text-center max-w-xl">
                                            * Algumas funcionalidades podem depender de integrações externas ou contratação adicional. Consulte nossa equipe para mais detalhes.
                                        </span>
                                    </div>
                                </section>
                            </main>
                        </div>

                        {/* Rodapé customizado */}
                        <footer className="w-full bg-gradient-to-tr from-blue-50 via-white to-blue-100 dark:from-gray-900 dark:via-gray-800 dark:to-gray-900 border-t border-gray-200 dark:border-gray-700 mt-16 shadow-inner">
                            <div className="max-w-7xl mx-auto px-4 py-14 sm:px-6 lg:px-8">
                                <div className="flex flex-col md:flex-row md:justify-between md:items-start gap-12">
                                    {/* Coluna 1 */}
                                    <div className="mb-8 md:mb-0 flex-1">
                                        <div className="flex items-center gap-3 mb-4">
                                            <svg
                                                xmlns="http://www.w3.org/2000/svg"
                                                className="h-10 w-10 text-blue-600"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                            >
                                                <rect x="7" y="7" width="10" height="10" rx="2" strokeWidth="2" />
                                                <path strokeWidth="2" d="M11 11h2v2h-2z" />
                                                <path strokeWidth="2" d="M4 8V4m0 0h4M4 4l4 4M20 8V4m0 0h-4m4 0l-4 4M4 16v4m0 0h4m-4 0l4-4M20 16v4m0 0h-4m4 0l-4-4" />
                                            </svg>
                                            <span className="text-2xl font-extrabold text-blue-700 tracking-tight">
                                                Ssait Odonto
                                            </span>
                                        </div>
                                        <p className="text-gray-700 dark:text-gray-300 max-w-xs mb-6">
                                            Soluções digitais sob medida para clínicas odontológicas modernas.
                                        </p>
                                        <div className="flex space-x-4 mt-6">
                                            <a
                                                href="#"
                                                className="text-blue-600 hover:text-blue-800 transition-colors"
                                                aria-label="Facebook"
                                            >
                                                <i className="fab fa-facebook-f text-2xl"></i>
                                            </a>
                                            <a
                                                href="#"
                                                className="text-pink-500 hover:text-pink-700 transition-colors"
                                                aria-label="Instagram"
                                            >
                                                <i className="fab fa-instagram text-2xl"></i>
                                            </a>
                                            <a
                                                href="#"
                                                className="text-red-600 hover:text-red-800 transition-colors"
                                                aria-label="YouTube"
                                            >
                                                <i className="fab fa-youtube text-2xl"></i>
                                            </a>
                                            <a
                                                href="#"
                                                className="text-blue-700 hover:text-blue-900 transition-colors"
                                                aria-label="LinkedIn"
                                            >
                                                <i className="fab fa-linkedin-in text-2xl"></i>
                                            </a>
                                        </div>
                                        {/* Horário de atendimento Suporte técnico */}
                                        <div className="mt-8 bg-blue-100 dark:bg-gray-800 rounded-xl p-5 text-base text-gray-700 dark:text-gray-200 shadow flex flex-col gap-1">
                                            <span className="font-bold text-blue-700 dark:text-blue-300 text-lg mb-1">
                                                Horário de atendimento Suporte técnico
                                            </span>
                                            <span className="flex items-center gap-2">
                                                <i className="far fa-clock text-blue-600"></i>
                                                Segunda a sexta: <span className="font-semibold">7h às 20h</span>
                                            </span>
                                            <span className="flex items-center gap-2">
                                                <i className="far fa-clock text-blue-600"></i>
                                                Sábado: <span className="font-semibold">7h às 13h</span>
                                            </span>
                                        </div>
                                    </div>
                                    {/* Coluna 2 */}
                                    <div className="grid grid-cols-2 md:grid-cols-3 gap-8 flex-1">
                                        <div>
                                            <h3 className="font-semibold text-blue-600 mb-3 uppercase tracking-wide">
                                                Suporte
                                            </h3>
                                            <ul className="space-y-2 text-gray-800 dark:text-gray-200 text-sm">
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Pedir demonstração
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Tire suas dúvidas
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Central de ajuda
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-blue-600 mb-3 uppercase tracking-wide">
                                                Institucional
                                            </h3>
                                            <ul className="space-y-2 text-gray-800 dark:text-gray-200 text-sm">
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Sobre nós
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Blog SSait
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                        <div>
                                            <h3 className="font-semibold text-blue-600 mb-3 uppercase tracking-wide">
                                                Produto
                                            </h3>
                                            <ul className="space-y-2 text-gray-800 dark:text-gray-200 text-sm">
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Planos e preços
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Recursos
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Contrato Odontológico
                                                    </a>
                                                </li>
                                                <li>
                                                    <a href="#" className="hover:text-blue-600 transition-colors">
                                                        Depoimentos
                                                    </a>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>
                                <div className="border-t border-gray-200 dark:border-gray-700 mt-12 pt-6 flex flex-col md:flex-row items-center justify-between text-gray-500 dark:text-gray-400 text-xs">
                                    <div className="flex items-center gap-2">
                                        <span>
                                            © 2025{' '}
                                            <span className="font-bold text-blue-700">
                                                SSait Tecnologia
                                            </span>{' '}
                                            – Todos os direitos reservados
                                        </span>
                                        <span className="mx-2 hidden md:inline">|</span>
                                        <a
                                            href="/termo-pedrao.pdf"
                                            className="hover:text-blue-600 transition-colors"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Termos de uso
                                        </a>
                                        <span className="mx-2 hidden md:inline">|</span>
                                        <a
                                            href="/politica-privacidade.pdf"
                                            className="hover:text-blue-600 transition-colors"
                                            target="_blank"
                                            rel="noopener noreferrer"
                                        >
                                            Política de Privacidade
                                        </a>
                                    </div>
                                    <div className="flex space-x-4 mt-4 md:mt-0">
                                        <a
                                            href="#"
                                            className="text-blue-600 hover:text-blue-800"
                                        >
                                            <i className="fab fa-facebook-f"></i>
                                        </a>
                                        <a
                                            href="#"
                                            className="text-pink-500 hover:text-pink-700"
                                        >
                                            <i className="fab fa-instagram"></i>
                                        </a>
                                        <a
                                            href="#"
                                            className="text-red-600 hover:text-red-800"
                                        >
                                            <i className="fab fa-youtube"></i>
                                        </a>
                                        <a
                                            href="#"
                                            className="text-blue-700 hover:text-blue-900"
                                        >
                                            <i className="fab fa-linkedin-in"></i>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </footer>
                    </div>
                </div>
            </div>
        </>
    );
}
