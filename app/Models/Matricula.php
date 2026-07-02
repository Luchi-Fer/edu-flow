<?php

namespace App\Models;

use App\Enums\EstadoMatricula;
use Database\Factories\MatriculaFactory;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\Pivot;

class Matricula extends Pivot
{
    /** @use HasFactory<MatriculaFactory> */
    use HasFactory;

    public $incrementing = true;

    protected $table = 'matriculas';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_matriculacion' => 'date',
            'estado' => EstadoMatricula::class,
        ];
    }

    /**
     * @return BelongsTo<Alumno, $this>
     */
    public function alumno(): BelongsTo
    {
        return $this->belongsTo(Alumno::class);
    }

    /**
     * @return BelongsTo<Curso, $this>
     */
    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }
}
