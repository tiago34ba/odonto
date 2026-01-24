<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // User::factory(10)->create();

        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        \App\Models\Paciente::factory(100)->create();

        $this->call([
            EmployeeSeeder::class,
            ProcedureSeeder::class,
            AgreementSeeder::class,
            SupplierSeeder::class,
            FormaPagamentoSeeder::class,
            GroupAnamneseSeeder::class,
            AnamneseSeeder::class,
            SchedulingSeeder::class, // Por último porque depende de outros dados
            ReportSeeder::class, // Relatórios baseados nos dados existentes
            ContasPagarSeeder::class, // Sistema financeiro
            ContasReceberSeeder::class, // Sistema financeiro
            FluxoCaixaSeeder::class, // Por último para incluir movimentações automáticas
        ]);
    }
}
