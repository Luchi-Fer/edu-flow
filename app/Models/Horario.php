<?php

namespace App\Models;

use App\Enums\DiaSemana;
use Database\Factories\HorarioFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['curso_materia_id', 'dia_semana', 'hora_inicio', 'hora_fin'])]
class Horario extends Model
{
    /** @use HasFactory<HorarioFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'dia_semana' => DiaSemana::class,
        ];
    }

    /**
     * @return BelongsTo<CursoMateria, $this>
     */
    public function cursoMateria(): BelongsTo
    {
        return $this->belongsTo(CursoMateria::class);
    }
}
