<?php

namespace App\Enums;

enum CondicionIvaUnidad: string
{
    case ConsumidorFinal = 'consumidor_final';
    case ResponsableInscripto = 'responsable_inscripto';
    case Exento = 'exento';

    public function label(): string
    {
        return match ($this) {
            self::ConsumidorFinal => 'Consumidor Final',
            self::ResponsableInscripto => 'Responsable Inscripto',
            self::Exento => 'Exento',
        };
    }
}
