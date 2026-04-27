<?php

namespace App\Enums;

enum CondicionIvaConsorcio: string
{
    case NoAlcanzado = 'no_alcanzado';
    case Exento = 'exento';
    case ResponsableInscripto = 'responsable_inscripto';

    public function label(): string
    {
        return match ($this) {
            self::NoAlcanzado => 'IVA No Alcanzado',
            self::Exento => 'Exento',
            self::ResponsableInscripto => 'Responsable Inscripto',
        };
    }
}
