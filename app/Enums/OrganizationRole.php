<?php

namespace App\Enums;

enum OrganizationRole: string
{
    case PropertyManager = 'property_manager';
    case Owner = 'owner';
    case Tenant = 'tenant';
    case Contractor = 'contractor';

    public function label(): string
    {
        return match ($this) {
            self::PropertyManager => 'Property Manager',
            self::Owner => 'Owner',
            self::Tenant => 'Tenant',
            self::Contractor => 'Contractor',
        };
    }
}
