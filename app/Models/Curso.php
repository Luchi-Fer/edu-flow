<?php

namespace App\Models;

use App\Enums\NivelEducativo;
use Database\Factories\CursoFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['ciclo_lectivo_id', 'nivel', 'anio_grado', 'division', 'turno'])]
class Curso extends Model
{
    /** @use HasFactory<CursoFactory> */
    use HasFactory;

    /** @var list<string> */
    protected $appends = ['label'];

    /**
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'nivel' => NivelEducativo::class,
        ];
    }

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
            ->withPivot('id', 'profesor_id')
            ->withTimestamps();
    }

    /**
     * @return BelongsToMany<Preceptor, $this>
     */
    public function preceptores(): BelongsToMany
    {
        return $this->belongsToMany(Preceptor::class, 'curso_preceptor')->withTimestamps();
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

    /**
     * @return Attribute<string, never>
     */
    protected function label(): Attribute
    {
        return Attribute::make(
            get: fn () => self::etiquetaAnioGrado($this->nivel, $this->anio_grado).' '.$this->division,
        );
    }

    protected static function etiquetaAnioGrado(NivelEducativo $nivel, int $anioGrado): string
    {
        return $nivel->etiquetasAnioGrado()[$anioGrado - 1] ?? "{$anioGrado}°";
    }
}
