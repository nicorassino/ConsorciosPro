<?php

namespace App\Enums;

enum RubroConceptoPresupuesto: string
{
    case Servicios = 'servicios';
    case Mantenimiento = 'mantenimiento';
    case Sueldos = 'sueldos';
    case Impuestos = 'impuestos';
    case Seguros = 'seguros';
    case Otros = 'otros';

    public function label(): string
    {
        return match ($this) {
            self::Servicios => 'Servicios',
            self::Mantenimiento => 'Mantenimiento',
            self::Sueldos => 'Sueldos',
            self::Impuestos => 'Impuestos',
            self::Seguros => 'Seguros',
            self::Otros => 'Otros',
        };
    }
}
