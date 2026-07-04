<?php

namespace App\Http\Requests;

use App\Models\Curso;
use App\Models\CursoMateria;
use App\Models\Horario;
use App\Models\Materia;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * @property-read Curso $curso
 * @property-read Materia $materia
 */
class StoreHorarioRequest extends FormRequest
{
    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'dia_semana' => ['required', Rule::in(['lunes', 'martes', 'miercoles', 'jueves', 'viernes', 'sabado', 'domingo'])],
            'hora_inicio' => ['required', 'date_format:H:i'],
            'hora_fin' => ['required', 'date_format:H:i', 'after:hora_inicio'],
        ];
    }

    /**
     * Configure the validator instance.
     */
    public function withValidator(Validator $validator): void
    {
        $validator->after(function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $diaSemana = $this->string('dia_semana')->toString();
            $horaInicio = $this->string('hora_inicio')->toString();
            $horaFin = $this->string('hora_fin')->toString();

            $seSuperponen = fn ($query) => $query
                ->where('dia_semana', $diaSemana)
                ->where('hora_inicio', '<', $horaFin)
                ->where('hora_fin', '>', $horaInicio);

            $superpuestoEnElCurso = Horario::whereHas(
                'cursoMateria',
                fn ($query) => $query->where('curso_id', $this->curso->id),
            )->where($seSuperponen)->exists();

            if ($superpuestoEnElCurso) {
                $validator->errors()->add('hora_inicio', __('El curso ya tiene una clase en ese horario.'));

                return;
            }

            $profesorId = CursoMateria::where('curso_id', $this->curso->id)
                ->where('materia_id', $this->materia->id)
                ->value('profesor_id');

            if ($profesorId === null) {
                return;
            }

            $superpuestoParaElProfesor = Horario::whereHas(
                'cursoMateria',
                fn ($query) => $query->where('profesor_id', $profesorId),
            )->where($seSuperponen)->exists();

            if ($superpuestoParaElProfesor) {
                $validator->errors()->add('hora_inicio', __('El profesor ya tiene otra clase en ese horario.'));
            }
        });
    }
}
