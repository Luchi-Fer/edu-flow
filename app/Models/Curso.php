<?php

namespace App\Models;

use Database\Factories\CursoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ciclo_lectivo_id', 'anio', 'division', 'turno'])]
class Curso extends Model
{
    /** @use HasFactory<CursoFactory> */
    use HasFactory;

    /**
     * @return BelongsTo<CicloLectivo, $this>
     */
    public function cicloLectivo(): BelongsTo
    {
        return $this->belongsTo(CicloLectivo::class);
    }

    /**
     * @return HasMany<CursoMateria, $this>
     */
    public function cursoMaterias(): HasMany
    {
        return $this->hasMany(CursoMateria::class);
    }

    /**
     * @return BelongsToMany<Materia, $this, CursoMateria>
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'curso_materia')
            ->using(CursoMateria::class)
            ->withPivot('profesor_id')
            ->withTimestamps();
    }

    /**
     * @return HasMany<Matricula, $this>
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    /**
     * @return BelongsToMany<Alumno, $this, Matricula>
     */
    public function alumnos(): BelongsToMany
    {
        return $this->belongsToMany(Alumno::class, 'matriculas')
            ->using(Matricula::class)
            ->withPivot(['fecha_matriculacion', 'estado'])
            ->withTimestamps();
    }

    public function label(): string
    {
        return "{$this->anio}° {$this->division}";
    }
}
