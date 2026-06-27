<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class ReprogramacionMasivaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->user()->rol === 'admin';
    }

    public function rules(): array
    {
        return [
            // ── Precondición del SRS: al menos UN filtro debe venir ──
            'id_medico' => ['nullable', 'exists:medicos,id_medico'],
            'id_sede' => ['nullable', 'exists:sedes,id_sede'],
            'fecha_desde' => ['nullable', 'date'],
            'fecha_hasta' => ['nullable', 'date', 'after_or_equal:fecha_desde'],
            'motivo' => ['required', 'string', 'max:255'],
            'accion' => ['required', 'in:A,B'], // A = fecha fija, B = token de reprogramación
            // Solo obligatorios si accion=A (Opción A: nueva fecha/hora fija)
            'nueva_fecha' => ['required_if:accion,A', 'nullable', 'date', 'after:today'],
            'nueva_hora_inicio' => ['required_if:accion,A', 'nullable', 'date_format:H:i'],
            'confirma_impacto' => ['required', 'accepted'], // checkbox del paso 1 del SRS
        ];
    }

    /**
     * Validación adicional: al menos un filtro de selección debe estar
     * presente (precondición explícita del SRS — sin esto, el admin
     * podría reprogramar TODAS las citas del sistema por accidente).
     */
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $tieneAlgunFiltro = $this->filled('id_medico')
                || $this->filled('id_sede')
                || ($this->filled('fecha_desde') && $this->filled('fecha_hasta'));

            if (!$tieneAlgunFiltro) {
                $validator->errors()->add('id_medico',
                    'Debe proporcionar al menos un criterio de filtro (médico, sede o rango de fechas).');
            }
        });
    }

    public function messages(): array
    {
        return [
            'nueva_fecha.required_if' => 'Debe especificar la nueva fecha para la Opción A.',
            'confirma_impacto.accepted' => 'Debe confirmar que entiende el impacto de esta operación.',
        ];
    }
}
