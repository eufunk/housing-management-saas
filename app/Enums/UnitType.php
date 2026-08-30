<?php

namespace App\Enums;

enum UnitType: string
{
    case Apartment = 'apartment';
    case Commercial = 'commercial';
    case ParkingSpace = 'parking_space';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::Apartment => 'Wohnung',
            self::Commercial => 'Gewerbeeinheit',
            self::ParkingSpace => 'Stellplatz',
            self::Other => 'Sonstige Einheit',
        };
    }
}
