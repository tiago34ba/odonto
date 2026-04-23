<?php

namespace Database\Seeders;

use App\Models\GrupoAcesso;
use Illuminate\Database\Seeder;

class GrupoAcessoSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $grupos = [
            [
                'nome' => 'Grupo API Senior',
                'descricao' => 'Grupo criado por teste',
                'cor' => '#1D4ED8',
                'permissoes' => [
                    'usuarios.view',
                    'pacientes.create',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Grupo Teste 20260326213306',
                'descricao' => 'Grupo para validar login',
                'cor' => '#2563EB',
                'permissoes' => [
                    'DASHBOARD_VIEW',
                    'USERS_MANAGE',
                    'payment_access',
                    'pix_access',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Grupo Teste Login',
                'descricao' => 'Grupo para testes de login',
                'cor' => '#2563EB',
                'permissoes' => [
                    'DASHBOARD_VIEW',
                    'USERS_MANAGE',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Administrador',
                'descricao' => 'Grupo criado automaticamente pelo modulo Usuarios',
                'cor' => '#2563EB',
                'permissoes' => ['*'],
                'ativo' => true,
            ],
            [
                'nome' => 'Dentista',
                'descricao' => 'Grupo criado automaticamente pelo modulo Usuarios',
                'cor' => '#2563EB',
                'permissoes' => [
                    'DASHBOARD_VIEW',
                    'PATIENTS_VIEW',
                    'PATIENTS_MANAGE',
                    'SCHEDULINGS_VIEW',
                    'PROCEDURES_MANAGE',
                    'ODONTOGRAM_VIEW',
                    'ODONTOGRAM_MANAGE',
                    'TREATMENTS_MANAGE',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Secretária',
                'descricao' => 'Grupo criado automaticamente pelo modulo Usuarios',
                'cor' => '#2563EB',
                'permissoes' => [
                    'DASHBOARD_VIEW',
                    'PATIENTS_VIEW',
                    'PATIENTS_MANAGE',
                    'SCHEDULINGS_VIEW',
                    'SCHEDULINGS_MANAGE',
                    'FINANCE_RECEIVABLE_VIEW',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Auxiliar Dentista',
                'descricao' => 'Grupo criado automaticamente pelo modulo Usuarios',
                'cor' => '#2563EB',
                'permissoes' => [
                    'DASHBOARD_VIEW',
                    'PATIENTS_VIEW',
                    'SCHEDULINGS_VIEW',
                    'ODONTOGRAM_VIEW',
                    'TREATMENTS_ASSIST',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Faxineiro',
                'descricao' => 'Grupo criado automaticamente pelo modulo Usuarios',
                'cor' => '#2563EB',
                'permissoes' => [
                    'DASHBOARD_VIEW',
                    'TASKS_VIEW',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Financeiro',
                'descricao' => 'Grupo financeiro criado pelo modulo Usuarios',
                'cor' => '#0EA5E9',
                'permissoes' => [
                    'DASHBOARD_VIEW',
                    'FINANCE_DASHBOARD_VIEW',
                    'FINANCE_PAYABLE_VIEW',
                    'FINANCE_PAYABLE_MANAGE',
                    'FINANCE_RECEIVABLE_VIEW',
                    'FINANCE_RECEIVABLE_MANAGE',
                    'FINANCE_CASHFLOW_VIEW',
                    'FINANCE_REPORTS_VIEW',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'SaaS Admin',
                'descricao' => 'Perfil com administracao completa do portal SaaS.',
                'cor' => '#1D4ED8',
                'permissoes' => ['*'],
                'ativo' => true,
            ],
            [
                'nome' => 'Admin Clinica',
                'descricao' => 'Gestao completa da clinica e equipe.',
                'cor' => '#0F766E',
                'permissoes' => [
                    'dashboard.view',
                    'paciente.manage',
                    'agendamento.manage',
                    'financeiro.manage',
                    'relatorio.view',
                    'usuario.manage',
                ],
                'ativo' => true,
            ],
            [
                'nome' => 'Operacional',
                'descricao' => 'Acesso operacional para agenda e atendimento.',
                'cor' => '#7C3AED',
                'permissoes' => [
                    'dashboard.view',
                    'paciente.view',
                    'agendamento.manage',
                    'relatorio.view',
                ],
                'ativo' => true,
            ],
        ];

        foreach ($grupos as $grupo) {
            GrupoAcesso::updateOrCreate(
                ['nome' => $grupo['nome']],
                [
                    'descricao' => $grupo['descricao'],
                    'cor' => $grupo['cor'],
                    'permissoes' => $grupo['permissoes'],
                    'ativo' => $grupo['ativo'],
                ]
            );
        }
    }
}
