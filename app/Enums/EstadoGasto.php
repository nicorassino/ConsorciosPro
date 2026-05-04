<?php

namespace App\Enums;

enum EstadoGasto: string
{
    case Pendiente = 'pendiente';
    case Pagado = 'pagado';

    public function label(): string
    {
        return match ($this) {
            self::Pendiente => 'Pendiente',
            self::Pagado => 'Pagado',
        };
    }
}
