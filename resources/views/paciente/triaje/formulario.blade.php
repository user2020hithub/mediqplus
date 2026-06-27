@extends('layouts.app')

@section('titulo', 'Triaje Preventivo')

@section('contenido')
    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-body p-4 p-md-5">
                    <h1 class="h4 fw-bold mb-2 text-mediq-azul-oscuro">Triaje Preventivo</h1>
                    <p class="text-muted mb-4">
                        Esta información ayuda a su médico a preparar mejor su consulta del
                        <strong>{{ $cita->fecha_cita->format('d/m/Y') }}</strong>.
                        Los datos sensibles se almacenan cifrados y solo su médico puede consultarlos.
                    </p>

                    @if ($errors->any())
                        <div class="alert alert-danger">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('paciente.triaje.guardar', $cita->id_cita) }}">
                        @csrf

                        <div class="mb-3">
                            <label for="input-motivo" class="form-label fw-semibold">¿Cuál es el motivo de su
                                consulta?</label>
                            <textarea name="motivo_consulta" id="input-motivo" class="form-control" rows="3" required></textarea>
                        </div>

                        <div class="mb-3">
                            <label class="form-label fw-semibold">¿Qué síntomas presenta?</label>
                            <div class="d-flex flex-wrap gap-3">
                                @foreach (['dolor' => 'Dolor', 'fiebre' => 'Fiebre', 'mareos' => 'Mareos', 'otro' => 'Otro'] as $valor => $texto)
                                    <div class="form-check">
                                        <input type="checkbox" name="sintomas[]" value="{{ $valor }}"
                                            class="form-check-input" id="s_{{ $valor }}">
                                        <label class="form-check-label"
                                            for="s_{{ $valor }}">{{ $texto }}</label>
                                    </div>
                                @endforeach
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="input-intensidad" class="form-label fw-semibold">Intensidad del malestar (1 = leve,
                                10 = muy intensa)</label>
                            <input type="range" name="intensidad" id="input-intensidad" class="form-range" min="1"
                                max="10" value="5"
                                oninput="document.getElementById('valorIntensidad').textContent = this.value">
                            <div class="text-center fw-semibold text-mediq-azul-medio">
                                <span id="valorIntensidad">5</span> / 10
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="input-tiene-alergias" class="form-label fw-semibold">¿Tiene alergias
                                conocidas?</label>
                            <select name="tiene_alergias" class="form-select" id="input-tiene-alergias" required>
                                <option value="0">No</option>
                                <option value="1">Sí</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="input-detalle-alergias" class="form-label fw-semibold">Detalle de alergias <span
                                    class="text-muted">(si aplica)</span></label>
                            <input type="text" name="detalle_alergias" id="input-detalle-alergias" class="form-control">
                        </div>

                        <div class="mb-4">
                            <label for="input-medicamentos" class="form-label fw-semibold">Medicamentos que toma actualmente
                                <span class="text-muted">(opcional)</span></label>
                            <input type="text" name="medicamentos_actuales" id="input-medicamentos" class="form-control">
                        </div>

                        <div class="form-check mb-4 p-3 rounded bg-mediq-azul-pale">
                            <input type="checkbox" name="acepta_disclaimer" class="form-check-input" id="disc"
                                required>
                            <label class="form-check-label" for="disc">
                                Entiendo que esta información es referencial y no sustituye la evaluación médica presencial.
                            </label>
                        </div>

                        <button type="submit" class="btn btn-primary btn-lg w-100 fw-semibold">Enviar Triaje</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
