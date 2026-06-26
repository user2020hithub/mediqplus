<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class RegistroPacienteRequest extends FormRequest
{
    public function authorize(): bool
    {
        // Cualquier visitante no autenticado puede intentar registrarse.
        return true;
    }

    public function rules(): array
    {
        return [
            'tipo_documento'     => ['required', Rule::in(['DNI', 'CE', 'CPP'])],
            'numero_documento'   => ['required', 'string', 'max:12'],
            'nombres_apellidos'  => ['required', 'string', 'max:100'],
            // before_or_equal valida mayoría de edad: la fecha debe ser
            // anterior o igual a hace 18 años desde hoy.
            'fecha_nacimiento'   => ['required', 'date', 'before_or_equal:' . now()->subYears(18)->format('Y-m-d')],
            'sexo'               => ['required', Rule::in(['Masculino', 'Femenino'])],
            'correo_electronico' => ['required', 'email', 'max:100', 'unique:usuarios,correo_electronico'],
            'telefono_movil'     => ['nullable', 'string', 'size:9'],

            // Política de contraseña del SRS: mín. 8 caract., 1 mayúscula, 1 número, 1 especial
            'password' => [
                'required',
                'string',
                'min:8',
                'confirmed',
                'regex:/^(?=.*[A-Z])(?=.*[0-9])(?=.*[\W_]).+$/',
            ],
            'acepta_privacidad' => ['required', 'accepted'],
        ];
    }

    // Validación adicional de unicidad combinada (tipo_documento + numero_documento)
    // que la tabla pacientes exige como UNIQUE compuesto (definido en la Fase 2).
    public function withValidator($validator): void
    {
        $validator->after(function ($validator) {
            $existe = \App\Models\Paciente::where('tipo_documento', $this->tipo_documento)
                ->where('numero_documento', $this->numero_documento)
                ->exists();

            if ($existe) {
                $validator->errors()->add(
                    'numero_documento',
                    'Este documento de identidad ya está asociado a una cuenta existente.'
                );
            }
        });
    }

    public function messages(): array
    {
        return [
            'fecha_nacimiento.before_or_equal' => 'Debe ser mayor de 18 años para registrarse de forma autónoma en esta plataforma.',
            'correo_electronico.unique' => 'Este correo electrónico ya está asociado a una cuenta existente.',
            'password.regex' => 'La contraseña debe tener al menos 1 mayúscula, 1 número y 1 carácter especial.',
            'acepta_privacidad.accepted' => 'Debe aceptar la Política de Privacidad para continuar.',
        ];
    }
}
