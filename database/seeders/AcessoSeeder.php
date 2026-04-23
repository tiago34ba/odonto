<?php

namespace Database\Seeders;

use App\Models\Acesso;
use App\Models\GrupoAcesso;
use Illuminate\Database\Seeder;

class AcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupoSaas = GrupoAcesso::where('nome', 'SaaS Admin')->first();
        $grupoClinica = GrupoAcesso::where('nome', 'Admin Clinica')->first();
        $grupoOperacional = GrupoAcesso::where('nome', 'Operacional')->first();

        $acessos = [
            [
                'nome' => 'Dashboard - Visualizar',
                'codigo' => 'dashboard.view',
                'descricao' => 'Permite visualizar indicadores do dashboard.',
                'categoria' => 'Dashboard',
                'nivel_risco' => 'baixo',
                'sistema_interno' => true,
                'ativo' => true,
                'grupo_acesso_id' => $grupoOperacional?->id,
            ],
            [
                'nome' => 'Pacientes - Gerenciar',
                'codigo' => 'paciente.manage',
                'descricao' => 'Permite criar, editar e excluir pacientes.',
                'categoria' => 'Pacientes',
                'nivel_risco' => 'medio',
                'sistema_interno' => true,
                'ativo' => true,
                'grupo_acesso_id' => $grupoClinica?->id,
            ],
            [
                'nome' => 'Agendamentos - Gerenciar',
                'codigo' => 'agendamento.manage',
                'descricao' => 'Permite gerenciar agenda e confirmacoes.',
                'categoria' => 'Agenda',
                'nivel_risco' => 'medio',
                'sistema_interno' => true,
                'ativo' => true,
                'grupo_acesso_id' => $grupoOperacional?->id,
            ],
            [
                'nome' => 'Financeiro - Gerenciar',
                'codigo' => 'financeiro.manage',
                'descricao' => 'Permite alterar contas e movimentos financeiros.',
                'categoria' => 'Financeiro',
                'nivel_risco' => 'alto',
                'sistema_interno' => true,
                'ativo' => true,
                'grupo_acesso_id' => $grupoClinica?->id,
            ],
            [
                'nome' => 'Relatorios - Visualizar',
                'codigo' => 'relatorio.view',
                'descricao' => 'Permite visualizar relatorios operacionais e gerenciais.',
                'categoria' => 'Relatorios',
                'nivel_risco' => 'baixo',
                'sistema_interno' => true,
                'ativo' => true,
                'grupo_acesso_id' => $grupoClinica?->id,
            ],
            [
                'nome' => 'Usuarios - Gerenciar',
                'codigo' => 'usuario.manage',
                'descricao' => 'Permite gerenciar usuarios e vinculo com grupos.',
                'categoria' => 'Seguranca',
                'nivel_risco' => 'critico',
                'sistema_interno' => true,
                'ativo' => true,
                'grupo_acesso_id' => $grupoClinica?->id,
            ],
            [
                'nome' => 'SaaS - Administracao Geral',
                'codigo' => 'saas.admin',
                'descricao' => 'Permite administrar assinaturas, planos e clientes do SaaS.',
                'categoria' => 'SaaS',
                'nivel_risco' => 'critico',
                'sistema_interno' => true,
                'ativo' => true,
                'grupo_acesso_id' => $grupoSaas?->id,
            ],
        ];

        foreach ($acessos as $acesso) {
            Acesso::updateOrCreate(
                ['codigo' => $acesso['codigo']],
                [
                    'nome' => $acesso['nome'],
                    'descricao' => $acesso['descricao'],
                    'categoria' => $acesso['categoria'],
                    'nivel_risco' => $acesso['nivel_risco'],
                    'sistema_interno' => $acesso['sistema_interno'],
                    'ativo' => $acesso['ativo'],
                    'grupo_acesso_id' => $acesso['grupo_acesso_id'],
                ]
            );
        }
    }
}
