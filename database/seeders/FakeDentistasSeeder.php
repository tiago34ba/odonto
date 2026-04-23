<?php

namespace Database\Seeders;

use App\Models\Dentista;
use Illuminate\Database\Seeder;

class FakeDentistasSeeder extends Seeder
{
    public function run(): void
    {
        $horariosBase = [
            ['dia_semana' => 'segunda', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'terca', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'quarta', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'quinta', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'sexta', 'ativo' => true, 'hora_inicio' => '08:00', 'hora_fim' => '18:00'],
            ['dia_semana' => 'sabado', 'ativo' => false, 'hora_inicio' => '08:00', 'hora_fim' => '12:00'],
            ['dia_semana' => 'domingo', 'ativo' => false, 'hora_inicio' => '08:00', 'hora_fim' => '12:00'],
        ];

        $dados = [
            [
                'name' => 'Dra. Ana Martins',
                'email' => 'ana.martins.fake@odonto.local',
                'telefone' => '11940001001',
                'celular' => '11990001001',
                'cpf' => '11122233344',
                'cro' => 'CROSP1001',
                'cro_uf' => 'SP',
                'especialidade' => 'Ortodontia',
                'intervalo_consulta' => 30,
                'chave_pix' => 'ana.martins@pix.local',
                'cep' => '01310930',
                'rua' => 'Av Paulista',
                'numero' => '1000',
                'complemento' => 'Sala 101',
                'bairro' => 'Bela Vista',
                'cidade' => 'Sao Paulo',
                'estado' => 'SP',
                'status' => true,
            ],
            [
                'name' => 'Dr. Bruno Almeida',
                'email' => 'bruno.almeida.fake@odonto.local',
                'telefone' => '21940001002',
                'celular' => '21990001002',
                'cpf' => '22233344455',
                'cro' => 'CRORJ1002',
                'cro_uf' => 'RJ',
                'especialidade' => 'Endodontia',
                'intervalo_consulta' => 45,
                'chave_pix' => 'bruno.almeida@pix.local',
                'cep' => '20040002',
                'rua' => 'Rua da Assembleia',
                'numero' => '220',
                'complemento' => 'Sala 305',
                'bairro' => 'Centro',
                'cidade' => 'Rio de Janeiro',
                'estado' => 'RJ',
                'status' => true,
            ],
            [
                'name' => 'Dra. Carla Nogueira',
                'email' => 'carla.nogueira.fake@odonto.local',
                'telefone' => '31940001003',
                'celular' => '31990001003',
                'cpf' => '33344455566',
                'cro' => 'CROMG1003',
                'cro_uf' => 'MG',
                'especialidade' => 'Implantodontia',
                'intervalo_consulta' => 60,
                'chave_pix' => 'carla.nogueira@pix.local',
                'cep' => '30140071',
                'rua' => 'Av Afonso Pena',
                'numero' => '1500',
                'complemento' => 'Conj 45',
                'bairro' => 'Centro',
                'cidade' => 'Belo Horizonte',
                'estado' => 'MG',
                'status' => true,
            ],
            [
                'name' => 'Dr. Diego Ferreira',
                'email' => 'diego.ferreira.fake@odonto.local',
                'telefone' => '71940001004',
                'celular' => '71990001004',
                'cpf' => '44455566677',
                'cro' => 'CROBA1004',
                'cro_uf' => 'BA',
                'especialidade' => 'Periodontia',
                'intervalo_consulta' => 30,
                'chave_pix' => 'diego.ferreira@pix.local',
                'cep' => '40020120',
                'rua' => 'Rua Chile',
                'numero' => '88',
                'complemento' => 'Andar 2',
                'bairro' => 'Comercio',
                'cidade' => 'Salvador',
                'estado' => 'BA',
                'status' => false,
            ],
            [
                'name' => 'Dra. Elisa Rocha',
                'email' => 'elisa.rocha.fake@odonto.local',
                'telefone' => '41940001005',
                'celular' => '41990001005',
                'cpf' => '55566677788',
                'cro' => 'CROPR1005',
                'cro_uf' => 'PR',
                'especialidade' => 'Clinico Geral',
                'intervalo_consulta' => 20,
                'chave_pix' => 'elisa.rocha@pix.local',
                'cep' => '80010000',
                'rua' => 'Rua XV de Novembro',
                'numero' => '450',
                'complemento' => 'Sala 12',
                'bairro' => 'Centro',
                'cidade' => 'Curitiba',
                'estado' => 'PR',
                'status' => true,
            ],
        ];

        foreach ($dados as $item) {
            Dentista::updateOrCreate(
                ['cro' => $item['cro']],
                array_merge($item, [
                    'data_cadastro' => now()->toDateString(),
                    'horarios_atendimento' => $horariosBase,
                ])
            );
        }
    }
}
