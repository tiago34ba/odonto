<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Índices para tabela pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            if (!$this->indexExists('pacientes', 'idx_pacientes_name')) {
                $table->index('name', 'idx_pacientes_name');
            }
            if (!$this->indexExists('pacientes', 'idx_pacientes_email')) {
                $table->index('email', 'idx_pacientes_email');
            }
            if (!$this->indexExists('pacientes', 'idx_pacientes_telefone')) {
                $table->index('telefone', 'idx_pacientes_telefone');
            }
            if (!$this->indexExists('pacientes', 'idx_pacientes_cpf_cnpj')) {
                $table->index('cpf_cnpj', 'idx_pacientes_cpf_cnpj');
            }
            if (!$this->indexExists('pacientes', 'idx_pacientes_created_at')) {
                $table->index('created_at', 'idx_pacientes_created_at');
            }
        });

        // Índices para tabela schedulings (agendamentos)
        Schema::table('schedulings', function (Blueprint $table) {
            if (!$this->indexExists('schedulings', 'idx_schedulings_patient_id')) {
                $table->index('patient_id', 'idx_schedulings_patient_id');
            }
            if (!$this->indexExists('schedulings', 'idx_schedulings_professional_id')) {
                $table->index('professional_id', 'idx_schedulings_professional_id');
            }
            if (!$this->indexExists('schedulings', 'idx_schedulings_procedure_id')) {
                $table->index('procedure_id', 'idx_schedulings_procedure_id');
            }
            if (!$this->indexExists('schedulings', 'idx_schedulings_date')) {
                $table->index('date', 'idx_schedulings_date');
            }
            if (!$this->indexExists('schedulings', 'idx_schedulings_status')) {
                $table->index('status', 'idx_schedulings_status');
            }
            if (!$this->indexExists('schedulings', 'idx_schedulings_date_status')) {
                $table->index(['date', 'status'], 'idx_schedulings_date_status');
            }
            if (!$this->indexExists('schedulings', 'idx_schedulings_created_at')) {
                $table->index('created_at', 'idx_schedulings_created_at');
            }
        });

        // Índices para tabela employees (funcionarios)
        Schema::table('employees', function (Blueprint $table) {
            if (!$this->indexExists('employees', 'idx_employees_name')) {
                $table->index('name', 'idx_employees_name');
            }
            if (!$this->indexExists('employees', 'idx_employees_email')) {
                $table->index('email', 'idx_employees_email');
            }
            if (!$this->indexExists('employees', 'idx_employees_role')) {
                $table->index('role', 'idx_employees_role');
            }
            if (!$this->indexExists('employees', 'idx_employees_active')) {
                $table->index('active', 'idx_employees_active');
            }
        });

        // Índices para tabela procedures (procedimentos)
        Schema::table('procedures', function (Blueprint $table) {
            if (!$this->indexExists('procedures', 'idx_procedures_name')) {
                $table->index('name', 'idx_procedures_name');
            }
            if (!$this->indexExists('procedures', 'idx_procedures_value')) {
                $table->index('value', 'idx_procedures_value');
            }
            if (!$this->indexExists('procedures', 'idx_procedures_active')) {
                $table->index('active', 'idx_procedures_active');
            }
            if (!$this->indexExists('procedures', 'idx_procedures_category')) {
                $table->index('category', 'idx_procedures_category');
            }
        });

        // Índices para tabela agreements (convênios) - se existir
        if (Schema::hasTable('agreements')) {
            Schema::table('agreements', function (Blueprint $table) {
                if (!$this->indexExists('agreements', 'idx_agreements_name')) {
                    $table->index('name', 'idx_agreements_name');
                }
            });
        }

        // Índices para tabela forma_pagamentos - se existir
        if (Schema::hasTable('forma_pagamentos')) {
            Schema::table('forma_pagamentos', function (Blueprint $table) {
                if (!$this->indexExists('forma_pagamentos', 'idx_forma_pagamentos_nome')) {
                    $table->index('nome', 'idx_forma_pagamentos_nome');
                }
            });
        }

        // Índices compostos para otimização de consultas específicas
        Schema::table('schedulings', function (Blueprint $table) {
            if (!$this->indexExists('schedulings', 'idx_schedulings_patient_date')) {
                $table->index(['patient_id', 'date'], 'idx_schedulings_patient_date');
            }
            if (!$this->indexExists('schedulings', 'idx_schedulings_professional_date')) {
                $table->index(['professional_id', 'date'], 'idx_schedulings_professional_date');
            }
        });

        // Índices para campos de auditoria e timestamp
        Schema::table('pacientes', function (Blueprint $table) {
            if (!$this->indexExists('pacientes', 'idx_pacientes_updated_at')) {
                $table->index('updated_at', 'idx_pacientes_updated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop índices da tabela pacientes
        Schema::table('pacientes', function (Blueprint $table) {
            $table->dropIndex('idx_pacientes_name');
            $table->dropIndex('idx_pacientes_email');
            $table->dropIndex('idx_pacientes_telefone');
            $table->dropIndex('idx_pacientes_cpf_cnpj');
            $table->dropIndex('idx_pacientes_created_at');
            $table->dropIndex('idx_pacientes_updated_at');
        });

        // Drop índices da tabela schedulings
        Schema::table('schedulings', function (Blueprint $table) {
            $table->dropIndex('idx_schedulings_patient_id');
            $table->dropIndex('idx_schedulings_professional_id');
            $table->dropIndex('idx_schedulings_procedure_id');
            $table->dropIndex('idx_schedulings_date');
            $table->dropIndex('idx_schedulings_status');
            $table->dropIndex('idx_schedulings_date_status');
            $table->dropIndex('idx_schedulings_created_at');
            $table->dropIndex('idx_schedulings_patient_date');
            $table->dropIndex('idx_schedulings_professional_date');
        });

        // Drop índices da tabela employees
        Schema::table('employees', function (Blueprint $table) {
            $table->dropIndex('idx_employees_name');
            $table->dropIndex('idx_employees_email');
            $table->dropIndex('idx_employees_role');
            $table->dropIndex('idx_employees_active');
        });

        // Drop índices da tabela procedures
        Schema::table('procedures', function (Blueprint $table) {
            $table->dropIndex('idx_procedures_name');
            $table->dropIndex('idx_procedures_value');
            $table->dropIndex('idx_procedures_active');
            $table->dropIndex('idx_procedures_category');
        });

        // Drop índices da tabela agreements - se existir
        if (Schema::hasTable('agreements')) {
            Schema::table('agreements', function (Blueprint $table) {
                $table->dropIndex('idx_agreements_name');
            });
        }

        // Drop índices da tabela forma_pagamentos - se existir
        if (Schema::hasTable('forma_pagamentos')) {
            Schema::table('forma_pagamentos', function (Blueprint $table) {
                $table->dropIndex('idx_forma_pagamentos_nome');
            });
        }
    }

    /**
     * Check if index exists
     */
    private function indexExists(string $table, string $index): bool
    {
        if (DB::getDriverName() === 'sqlite') {
            return collect(DB::select("PRAGMA index_list('{$table}')"))
                ->pluck('name')
                ->contains($index);
        }

        return collect(DB::select("SHOW INDEX FROM `{$table}`"))
            ->pluck('Key_name')
            ->contains($index);
    }
};
