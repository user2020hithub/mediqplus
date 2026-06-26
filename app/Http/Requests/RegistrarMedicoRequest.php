<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

// Form Request: valida los datos del formulario de alta/edición de médico
// antes de que lleguen al Controller (así el Controller queda limpio).
class RegistrarMedicoRequest extends FormRequest
{
    public function authorize(): bool
    {
        // El middleware 'role:admin' de la ruta ya filtra el acceso;
        // aquí se valida de nuevo por defensa en profundidad.
        return $this->user()->rol === 'admin';
    }

    public function rules(): array
    {
        // Si es edición, $this->route('medico') existe y se excluye de la
        // regla "unique" (para no chocar contra sí mismo al editar).
        $idMedico = $this->route('medico')?->id_medico;

        return [
            'nombres_apellidos' => ['required', 'string', 'max:100'],
            'dni' => ['required', 'string', 'size:8', 'unique:medicos,dni,' . $idMedico . ',id_medico'],
            'colegiatura' => ['required', 'string', 'max:15', 'unique:medicos,colegiatura,' . $idMedico . ',id_medico'],
            'correo_electronico' => ['required', 'email', 'max:100', 'unique:usuarios,correo_electronico'],
            'id_especialidad' => ['required', 'exists:especialidades,id_especialidad'],
            'telefono' => ['nullable', 'string', 'size:9'],
            'sedes' => ['required', 'array', 'min:1'], // debe tener al menos 1 sede asignada
            'sedes.*' => ['exists:sedes,id_sede'],
        ];
    }

    public function messages(): array
    {
        return [
            'dni.unique' => 'Este DNI ya está registrado en el sistema.',
            'colegiatura.unique' => 'Este número de colegiatura ya está registrado.',
            'correo_electronico.unique' => 'Este correo electrónico ya está asociado a una cuenta existente.',
            'sedes.min' => 'Debe asignar al médico a al menos una sede.',
        ];
    }
}
