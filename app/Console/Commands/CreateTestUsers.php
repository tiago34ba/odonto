<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateTestUsers extends Command
{
    protected $signature = 'users:create-test';
    protected $description = 'Cria 5 usuários de teste automaticamente';

    public function handle()
    {
        $users = [
            [
                'nome' => 'João',
                'sobrenome' => 'Silva',
                'email' => 'joao@teste.com',
                'telefone' => '11999999999',
                'clinica' => 'Odonto Teste',
                'especialidade' => 'Ortodontia',
                'password' => Hash::make('senha123'),
            ],
            [
                'nome' => 'Maria',
                'sobrenome' => 'Souza',
                'email' => 'maria@teste.com',
                'telefone' => '11988888888',
                'clinica' => 'Odonto Teste',
                'especialidade' => 'Implante',
                'password' => Hash::make('senha123'),
            ],
            [
                'nome' => 'Carlos',
                'sobrenome' => 'Oliveira',
                'email' => 'carlos@teste.com',
                'telefone' => '11977777777',
                'clinica' => 'Odonto Teste',
                'especialidade' => 'Clínico Geral',
                'password' => Hash::make('senha123'),
            ],
            [
                'nome' => 'Ana',
                'sobrenome' => 'Ferreira',
                'email' => 'ana@teste.com',
                'telefone' => '11966666666',
                'clinica' => 'Odonto Teste',
                'especialidade' => 'Pediatria',
                'password' => Hash::make('senha123'),
            ],
            [
                'nome' => 'Pedro',
                'sobrenome' => 'Martins',
                'email' => 'pedro@teste.com',
                'telefone' => '11955555555',
                'clinica' => 'Odonto Teste',
                'especialidade' => 'Endodontia',
                'password' => Hash::make('senha123'),
            ],
        ];

        foreach ($users as $data) {
            User::updateOrCreate(['email' => $data['email']], $data);
        }

        $this->info('Usuários de teste criados com sucesso!');
    }
}
