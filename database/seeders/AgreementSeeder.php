<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use App\Models\Agreement;

class AgreementSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $agreements = [
            [
                'name' => 'Unimed',
                'phone' => '(11) 3456-7890',
                'clinic_commission' => 15.00,
            ],
            [
                'name' => 'Bradesco Saúde',
                'phone' => '(11) 3456-7891',
                'clinic_commission' => 12.50,
            ],
            [
                'name' => 'SulAmérica',
                'phone' => '(11) 3456-7892',
                'clinic_commission' => 18.00,
            ],
            [
                'name' => 'Amil',
                'phone' => '(11) 3456-7893',
                'clinic_commission' => 14.00,
            ],
            [
                'name' => 'Porto Seguro',
                'phone' => '(11) 3456-7894',
                'clinic_commission' => 16.50,
            ],
            [
                'name' => 'Golden Cross',
                'phone' => '(11) 3456-7895',
                'clinic_commission' => 13.75,
            ],
        ];

        foreach ($agreements as $agreementData) {
            Agreement::create($agreementData);
        }
    }
}
