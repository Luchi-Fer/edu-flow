<?php

namespace App\Enums;

enum NivelEducativo: string
{
    case Primaria = 'primaria';
    case Secundaria = 'secundaria';

    /**
     * Etiquetas del año/grado (posiciones 1 a 6) para este nivel.
     *
     * @return array<int, string>
     */
    public function etiquetasAnioGrado(): array
    {
        return match ($this) {
            self::Primaria => ['1er grado', '2do grado', '3er grado', '4to grado', '5to grado', '6to grado'],
            self::Secundaria => ['7mo grado', '8vo grado', '9no grado', '1er año', '2do año', '3er año'],
        };
    }
}
