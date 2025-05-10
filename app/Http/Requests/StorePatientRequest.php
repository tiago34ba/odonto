<?php
namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StorePatientRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Permitir todas as requisições
    }

    public function rules(): array
    {
        return [
            'id' => 'nullable|integer',
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'insurance' => 'nullable|string|max:255',
            'age' => 'nullable|integer|min:0',
            'nascimento' => 'nullable|date',
            'responsavel' => 'nullable|string|max:255',
            'cpfResponsavel' => 'nullable|string|max:14',
            'pessoa' => 'nullable|string|in:Física,Jurídica',
            'cpfCnpj' => 'nullable|string|max:18',
            'email' => 'nullable|email|max:255|unique:patients,email',
            'cep' => 'nullable|string|max:9',
            'rua' => 'nullable|string|max:255',
            'numero' => 'nullable|string|max:10',
            'complemento' => 'nullable|string|max:255',
            'bairro' => 'nullable|string|max:255',
            'cidade' => 'nullable|string|max:255',
            'estado' => 'nullable|string|max:2',
            'tipoSanguineo' => 'nullable|string|in:A+,A-,B+,B-,AB+,AB-,O+,O-',
            'sexo' => 'nullable|string|in:Masculino,Feminino,Outro',
            'profissao' => 'nullable|string|max:255',
            'estadoCivil' => 'nullable|string|in:Solteiro(a),Casado(a),Divorciado(a),Viúvo(a),Outro',
            'telefone2' => 'nullable|string|max:20',
            'observacoes' => 'nullable|string|max:1000',
        ];
    }
}
