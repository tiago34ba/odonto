<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Supplier;

class SupplierSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $suppliers = [
            [
                'name' => 'Dental Supply Brasil',
                'email' => 'contato@dentalsupply.com.br',
                'phone' => '(11) 3222-1111',
                'cnpj' => '12.345.678/0001-10',
                'pix' => 'contato@dentalsupply.com.br',
                'pix_key_type' => 'email',
                'street' => 'Rua das Flores',
                'number' => '123',
                'complement' => 'Sala 45',
                'neighborhood' => 'Centro',
                'city' => 'São Paulo',
                'state' => 'SP',
                'cep' => '01234-567',
            ],
            [
                'name' => 'Odonto Materiais Ltda',
                'email' => 'vendas@odontomateriais.com',
                'phone' => '(11) 3333-2222',
                'cnpj' => '23.456.789/0001-20',
                'pix' => '(11) 3333-2222',
                'pix_key_type' => 'phone',
                'street' => 'Avenida Brasil',
                'number' => '456',
                'complement' => null,
                'neighborhood' => 'Vila Nova',
                'city' => 'São Paulo',
                'state' => 'SP',
                'cep' => '02345-678',
            ],
            [
                'name' => 'Instrumentos Dentários SP',
                'email' => 'pedidos@instrumentosdent.com',
                'phone' => '(11) 4444-3333',
                'cnpj' => '34.567.890/0001-30',
                'pix' => '34567890000130',
                'pix_key_type' => 'cnpj',
                'street' => 'Rua Augusta',
                'number' => '789',
                'complement' => 'Andar 2',
                'neighborhood' => 'Consolação',
                'city' => 'São Paulo',
                'state' => 'SP',
                'cep' => '03456-789',
            ],
            [
                'name' => 'Dental Tech Equipment',
                'email' => 'info@dentaltech.com.br',
                'phone' => '(11) 5555-4444',
                'cnpj' => '45.678.901/0001-40',
                'pix' => 'f47ac10b-58cc-4372-a567-0e02b2c3d479',
                'pix_key_type' => 'random',
                'street' => 'Rua Paulista',
                'number' => '1000',
                'complement' => 'Conjunto 15',
                'neighborhood' => 'Bela Vista',
                'city' => 'São Paulo',
                'state' => 'SP',
                'cep' => '04567-890',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::create($supplierData);
        }
    }
}
