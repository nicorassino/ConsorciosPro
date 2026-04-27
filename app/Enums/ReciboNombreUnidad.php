<?php

namespace App\Enums;

enum ReciboNombreUnidad: string
{
    case Propietario = 'propietario';
    case Inmobiliaria = 'inmobiliaria';
    case Dueno = 'dueno';

    public function label(): string
    {
        return match ($this) {
            self::Propietario => 'Propietario',
            self::Inmobiliaria => 'Inmobiliaria',
            self::Dueno => 'Dueño',
        };
    }
}
