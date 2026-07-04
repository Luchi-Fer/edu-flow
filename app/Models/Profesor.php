<?php

namespace App\Models;

use Database\Factories\ProfesorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['user_id', 'dni', 'apellido', 'nombre', 'fecha_nacimiento', 'telefono', 'direccion', 'fecha_ingreso', 'activo'])]
class Profesor extends Model
{
    /** @use HasFactory<ProfesorFactory> */
    use HasFactory;

    protected $table = 'profesores';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_nacimiento' => 'date',
            'fecha_ingreso' => 'date',
            'activo' => 'boolean',
        ];
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @return HasMany<CursoMateria, $this>
     */
    public function asignaciones(): HasMany
    {
        return $this->hasMany(CursoMateria::class);
    }

    /**
     * @return BelongsToMany<Curso, $this, CursoMateria>
     */
    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_materia')
            ->using(CursoMateria::class)
            ->withPivot('id', 'materia_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Materia, $this, CursoMateria>
     */
    public function materias(): BelongsToMany
    {
        return $this->belongsToMany(Materia::class, 'curso_materia')
            ->using(CursoMateria::class)
            ->withPivot('id', 'curso_id')
            ->withTimestamps();
    }
}
