<?php

namespace App\Enums;

enum EstadoOcupacionUnidad: string
{
    case PropietarioResidente = 'propietario_residente';
    case Inquilino = 'inquilino';
    case Desocupado = 'desocupado';

    public function label(): string
    {
        return match ($this) {
            self::PropietarioResidente => 'Propietario residente',
            self::Inquilino => 'Inquilino',
            self::Desocupado => 'Desocupado',
        };
    }
}
