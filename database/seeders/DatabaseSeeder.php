<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            GrupoAcessoSeeder::class,
            AcessoSeeder::class,
        ]);

        // User::factory(10)->create();

        User::updateOrCreate(
            ['email' => 'test@example.com'],
            [
                'name' => 'Test User',
                'password' => Hash::make('password'),
                'email_verified_at' => now(),
            ]
        );

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
