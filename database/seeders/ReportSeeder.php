<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Report;
use Carbon\Carbon;

class ReportSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $now = Carbon::now();
        $thisMonth = $now->startOfMonth()->toDateString();
        $today = $now->toDateString();

        $reports = [
            [
                'name' => 'Relatório Geral do Sistema - Novembro 2025',
                'type' => 'system',
                'description' => 'Relatório completo com visão geral de todos os módulos do sistema odontológico',
                'data' => [
                    'summary' => [
                        'total_patients' => 106,
                        'total_appointments' => 5,
                        'total_procedures' => 8,
                        'total_employees' => 6,
                        'active_agreements' => 6
                    ],
                    'monthly_growth' => [
                        'patients' => ['this_month' => 0, 'last_month' => 106],
                        'appointments' => ['this_month' => 5, 'last_month' => 0]
                    ],
                    'daily_stats' => [
                        'appointments_today' => 0,
                        'completed_today' => 0,
                        'pending_today' => 0
                    ]
                ],
                'period_start' => $thisMonth,
                'period_end' => $today,
                'generated_by' => 'Sistema Automático',
                'is_scheduled' => true,
                'schedule_frequency' => 'monthly',
                'last_generated_at' => $now,
            ],
            [
                'name' => 'Relatório de Pacientes - Análise Demográfica',
                'type' => 'patients',
                'description' => 'Análise detalhada do perfil dos pacientes, distribuição geográfica e demográfica',
                'data' => [
                    'summary' => [
                        'total_patients' => 106,
                        'new_patients_period' => 0,
                        'average_age' => 35.5
                    ],
                    'demographics' => [
                        'gender_distribution' => [
                            ['sexo' => 'M', 'total' => 52],
                            ['sexo' => 'F', 'total' => 54]
                        ],
                        'city_distribution' => [
                            ['cidade' => 'São Paulo', 'total' => 45],
                            ['cidade' => 'Rio de Janeiro', 'total' => 32],
                            ['cidade' => 'Belo Horizonte', 'total' => 29]
                        ]
                    ]
                ],
                'period_start' => $thisMonth,
                'period_end' => $today,
                'generated_by' => 'Dr. João Silva',
                'is_scheduled' => false,
                'last_generated_at' => $now->subDays(2),
            ],
            [
                'name' => 'Relatório de Agendamentos - Performance',
                'type' => 'appointments',
                'description' => 'Análise de performance dos agendamentos, taxa de comparecimento e horários de pico',
                'data' => [
                    'summary' => [
                        'total_appointments' => 5,
                        'completed_appointments' => 1,
                        'canceled_appointments' => 0,
                        'completion_rate' => 20.0,
                        'cancellation_rate' => 0.0
                    ],
                    'distributions' => [
                        'status_distribution' => [
                            ['status' => 'agendado', 'total' => 3],
                            ['status' => 'confirmado', 'total' => 1],
                            ['status' => 'realizado', 'total' => 1]
                        ],
                        'busy_hours' => [
                            ['time' => '09:00', 'total' => 1],
                            ['time' => '14:30', 'total' => 1],
                            ['time' => '10:15', 'total' => 1]
                        ]
                    ]
                ],
                'period_start' => $thisMonth,
                'period_end' => $today,
                'generated_by' => 'Ana Costa',
                'is_scheduled' => true,
                'schedule_frequency' => 'weekly',
                'last_generated_at' => $now->subDays(1),
            ],
            [
                'name' => 'Relatório Financeiro - Receitas e Procedimentos',
                'type' => 'financial',
                'description' => 'Análise financeira detalhada com receitas, procedimentos mais lucrativos e formas de pagamento',
                'data' => [
                    'summary' => [
                        'total_revenue' => 350.00,
                        'completed_procedures' => 1,
                        'average_procedure_value' => 350.00
                    ],
                    'procedures' => [
                        'top_procedures' => [
                            ['procedure_id' => 4, 'quantity' => 1, 'procedimento' => ['name' => 'Canal Radicular', 'value' => 350.00]]
                        ],
                        'total_procedure_types' => 8
                    ],
                    'payments' => [
                        'payment_distribution' => [
                            ['method' => 'PIX', 'amount' => 105.00, 'percentage' => 30],
                            ['method' => 'Cartão de Crédito à Vista', 'amount' => 24.15, 'percentage' => 6.9],
                            ['method' => 'Dinheiro', 'amount' => 105.00, 'percentage' => 30]
                        ]
                    ]
                ],
                'period_start' => $thisMonth,
                'period_end' => $today,
                'generated_by' => 'Sistema Financeiro',
                'is_scheduled' => true,
                'schedule_frequency' => 'monthly',
                'last_generated_at' => $now,
            ],
            [
                'name' => 'Relatório de Profissionais - Produtividade',
                'type' => 'staff',
                'description' => 'Análise de produtividade e performance dos profissionais da clínica',
                'data' => [
                    'summary' => [
                        'total_staff' => 6,
                        'active_staff' => 6,
                        'total_staff_appointments' => 5,
                        'total_completed_procedures' => 1
                    ],
                    'performance' => [
                        'top_performers' => [
                            ['name' => 'Dr. João Silva', 'completed_appointments' => 1, 'completion_rate' => 100],
                            ['name' => 'Dra. Maria Santos', 'completed_appointments' => 0, 'completion_rate' => 0]
                        ]
                    ],
                    'roles_distribution' => [
                        ['role' => 'Dentista', 'total' => 2],
                        ['role' => 'Ortodontista', 'total' => 1],
                        ['role' => 'Recepcionista', 'total' => 1],
                        ['role' => 'Auxiliar de Enfermagem', 'total' => 1],
                        ['role' => 'Higienista Dental', 'total' => 1]
                    ]
                ],
                'period_start' => $thisMonth,
                'period_end' => $today,
                'generated_by' => 'RH - Lucia Mendes',
                'is_scheduled' => true,
                'schedule_frequency' => 'monthly',
                'last_generated_at' => $now->subDays(3),
            ],
            [
                'name' => 'Relatório de Produtividade Mensal - Comparativo',
                'type' => 'productivity',
                'description' => 'Análise comparativa de produtividade entre mês atual e anterior',
                'data' => [
                    'current_month' => [
                        'period' => '2025-11',
                        'data' => [
                            'patients' => 0,
                            'appointments' => 5,
                            'completed_procedures' => 1,
                            'revenue' => 350.00
                        ]
                    ],
                    'last_month' => [
                        'period' => '2025-10',
                        'data' => [
                            'patients' => 106,
                            'appointments' => 0,
                            'completed_procedures' => 0,
                            'revenue' => 0
                        ]
                    ],
                    'growth_rates' => [
                        'patients' => -100,
                        'appointments' => 0,
                        'completed_procedures' => 0,
                        'revenue' => 0
                    ]
                ],
                'period_start' => $now->startOfMonth()->toDateString(),
                'period_end' => $today,
                'generated_by' => 'Sistema Automático',
                'is_scheduled' => true,
                'schedule_frequency' => 'monthly',
                'last_generated_at' => $now,
            ]
        ];

        foreach ($reports as $reportData) {
            Report::create($reportData);
        }
    }
}
