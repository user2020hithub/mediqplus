<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class SuscribirseListaEsperaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // cualquier paciente autenticado puede suscribirse
    }

    public function rules(): array
    {
        return [
            'id_especialidad' => ['required', 'exists:especialidades,id_especialidad'],
            'id_sede' => ['required', 'exists:sedes,id_sede'],
            'fecha_inicio_pref' => ['nullable', 'date', 'after_or_equal:today'],
            // Flujo alternativo 4a del SRS: fecha_fin no puede ser anterior a fecha_inicio
            'fecha_fin_pref' => ['nullable', 'date', 'after_or_equal:fecha_inicio_pref'],
            'consentimiento' => ['required', 'accepted'], // paso 1 del SRS
        ];
    }

    public function messages(): array
    {
        return [
            'fecha_inicio_pref.after_or_equal' => 'La fecha de inicio no puede estar en el pasado.',
            'fecha_fin_pref.after_or_equal' => 'La fecha de inicio no puede ser posterior a la fecha de fin, ni estar en el pasado.',
            'consentimiento.accepted' => 'Debe marcar el consentimiento para continuar.',
        ];
    }
}
