<?php

namespace App\Models;

use Database\Factories\AlumnoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'user_id', 'dni', 'apellido', 'nombre', 'fecha_nacimiento', 'genero',
    'direccion', 'telefono', 'nombre_tutor', 'telefono_tutor', 'email_tutor',
    'fecha_ingreso', 'activo',
])]
class Alumno extends Model
{
    /** @use HasFactory<AlumnoFactory> */
    use HasFactory;

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
     * @return HasMany<Matricula, $this>
     */
    public function matriculas(): HasMany
    {
        return $this->hasMany(Matricula::class);
    }

    /**
     * @return BelongsToMany<Curso, $this, Matricula>
     */
    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'matriculas')
            ->using(Matricula::class)
            ->withPivot(['fecha_matriculacion', 'estado'])
            ->withTimestamps();
    }
}
