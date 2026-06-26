<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class TriajeRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // la propiedad del recurso se valida en el Controller (paso 5 del SRS)
    }

    public function rules(): array
    {
        return [
            'motivo_consulta' => ['required', 'string', 'max:500'],
            'sintomas' => ['required', 'array', 'min:1'],
            'intensidad' => ['required', 'integer', 'between:1,10'], // flujo 4a del SRS
            'tiene_alergias' => ['required', 'boolean'],
            // Condicional: solo obligatorio si tiene_alergias=true (paso 4 del SRS)
            'detalle_alergias' => ['required_if:tiene_alergias,true', 'nullable', 'string', 'max:255'],
            'medicamentos_actuales' => ['nullable', 'string', 'max:255'],
            'acepta_disclaimer' => ['required', 'accepted'],
        ];
    }

    public function messages(): array
    {
        return [
            'intensidad.between' => 'La intensidad debe estar entre 1 y 10.',
            'detalle_alergias.required_if' => 'Debe especificar el detalle de sus alergias.',
            'acepta_disclaimer.accepted' => 'Debe aceptar el disclaimer para continuar.',
        ];
    }
}
