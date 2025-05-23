<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SchedulingRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'patient_id'      => 'required|integer|exists:patients,id',
            'professional_id' => 'required|integer|exists:professionals,id',
            'procedure_id'    => 'required|integer|exists:procedures,id',
            'date'            => 'required|date',
            'time'            => 'required|string|max:5',
            'return'          => 'required|boolean',
            'obs'             => 'nullable|string|max:100',
        ];
    }
}
