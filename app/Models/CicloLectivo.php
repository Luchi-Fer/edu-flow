<?php

namespace App\Models;

use Database\Factories\CicloLectivoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['anio', 'fecha_inicio', 'fecha_fin', 'activo'])]
class CicloLectivo extends Model
{
    /** @use HasFactory<CicloLectivoFactory> */
    use HasFactory;

    protected $table = 'ciclos_lectivos';

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'activo' => 'boolean',
        ];
    }

    /**
     * @return HasMany<Curso, $this>
     */
    public function cursos(): HasMany
    {
        return $this->hasMany(Curso::class);
    }
}
