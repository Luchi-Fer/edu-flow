<?php

namespace App\Models;

use Database\Factories\MateriaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property-read CursoMateria|null $pivot
 */
#[Fillable(['nombre', 'descripcion'])]
class Materia extends Model
{
    /** @use HasFactory<MateriaFactory> */
    use HasFactory;

    /**
     * @return BelongsToMany<Curso, $this, CursoMateria>
     */
    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_materia')
            ->using(CursoMateria::class)
            ->withPivot('id', 'profesor_id')
            ->withTimestamps();
    }
}
