<?php

namespace App\Enums;

enum TipoConceptoPresupuesto: string
{
    case Ordinario = 'ordinario';
    case Extraordinario = 'extraordinario';

    public function label(): string
    {
        return match ($this) {
            self::Ordinario => 'Ordinario',
            self::Extraordinario => 'Extraordinario',
        };
    }
}
