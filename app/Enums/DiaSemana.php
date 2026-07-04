<?php

namespace App\Enums;

enum DiaSemana: string
{
    case Lunes = 'lunes';
    case Martes = 'martes';
    case Miercoles = 'miercoles';
    case Jueves = 'jueves';
    case Viernes = 'viernes';
    case Sabado = 'sabado';
    case Domingo = 'domingo';

    public function orden(): int
    {
        return match ($this) {
            self::Lunes => 1,
            self::Martes => 2,
            self::Miercoles => 3,
            self::Jueves => 4,
            self::Viernes => 5,
            self::Sabado => 6,
            self::Domingo => 7,
        };
    }

    public function etiqueta(): string
    {
        return match ($this) {
            self::Lunes => 'Lunes',
            self::Martes => 'Martes',
            self::Miercoles => 'Miércoles',
            self::Jueves => 'Jueves',
            self::Viernes => 'Viernes',
            self::Sabado => 'Sábado',
            self::Domingo => 'Domingo',
        };
    }
}
