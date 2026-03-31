<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Paciente>
 */
class PacienteFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $sexos = ['Masculino', 'Feminino', 'Outro'];
        $estados = ['AC','AL','AP','AM','BA','CE','DF','ES','GO','MA','MT','MS','MG','PA','PB','PR','PE','PI','RJ','RN','RS','RO','RR','SC','SP','SE','TO'];
        $estadosCivis = ['Solteiro(a)', 'Casado(a)', 'Divorciado(a)', 'Viúvo(a)', 'Outro'];
        $tiposSanguineos = ['A+', 'A-', 'B+', 'B-', 'AB+', 'AB-', 'O+', 'O-'];
        $pessoas = ['Física', 'Jurídica'];
        $convenios = ['Amil', 'Bradesco Saúde', 'Hapvida', 'Unimed Nacional', 'Sul América', 'Particular'];

        return [
            'name' => $this->faker->name(),
            'convenio' => $this->faker->randomElement($convenios),
            'telefone' => $this->faker->phoneNumber(),
            'idade' => $this->faker->numberBetween(1, 99),
            'data_nascimento' => $this->faker->date('Y-m-d', '-10 years'),
            'responsavel' => $this->faker->optional()->name(),
            'cpf_responsavel' => $this->faker->optional()->numerify('###.###.###-##'),
            'celular' => $this->faker->optional()->phoneNumber(),
            'estado' => $this->faker->randomElement($estados),
            'sexo' => $this->faker->randomElement($sexos),
            'profissao' => $this->faker->optional()->jobTitle(),
            'estado_civil' => $this->faker->randomElement($estadosCivis),
            'tipo_sanguineo' => $this->faker->randomElement($tiposSanguineos),
            'pessoa' => $this->faker->randomElement($pessoas),
            'cpf_cnpj' => $this->faker->unique()->numerify('###.###.###-##'),
            'email' => $this->faker->unique()->safeEmail(),
            'cep' => $this->faker->postcode(),
            'rua' => $this->faker->streetName(),
            'numero' => $this->faker->buildingNumber(),
            'complemento' => $this->faker->optional()->secondaryAddress(),
            'bairro' => $this->faker->citySuffix(),
            'observacoes' => $this->faker->optional()->sentence(),
        ];
    }
}
