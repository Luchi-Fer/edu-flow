<?php

namespace App\Enums;

enum EstadoMatricula: string
{
    case Activo = 'activo';
    case Baja = 'baja';
    case Egresado = 'egresado';
}
