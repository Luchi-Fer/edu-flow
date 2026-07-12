<?php

namespace App\Models;

use Database\Factories\PreceptorFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

#[Fillable(['user_id', 'dni', 'apellido', 'nombre', 'fecha_nacimiento', 'telefono', 'direccion', 'fecha_ingreso', 'activo'])]
class Preceptor extends Model
{
    /** @use HasFactory<PreceptorFactory> */
    use HasFactory;

    protected $table = 'preceptores';

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
     * @return BelongsToMany<Curso, $this>
     */
    public function cursos(): BelongsToMany
    {
        return $this->belongsToMany(Curso::class, 'curso_preceptor')->withTimestamps();
    }
}
