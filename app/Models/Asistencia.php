<?php

namespace App\Models;

use App\Enums\EstadoAsistencia;
use Database\Factories\AsistenciaFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['matricula_id', 'fecha', 'estado', 'registrado_por'])]
class Asistencia extends Model
{
    /** @use HasFactory<AsistenciaFactory> */
    use HasFactory;

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'estado' => EstadoAsistencia::class,
        ];
    }

    /**
     * @return BelongsTo<Matricula, $this>
     */
    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    /**
     * @return BelongsTo<User, $this>
     */
    public function registradoPor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'registrado_por');
    }
}
