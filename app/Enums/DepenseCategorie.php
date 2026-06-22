<?php

namespace App\Enums;

enum DepenseCategorie: string
{
    case ALIMENTAIRE = 'alimentaire';
    case BOISSONS = 'boissons';
    case HYGIENE = 'hygiène';
    case ENTRETIEN = 'entretien';
    case AUTRE = 'autre';

    public function label(): string
    {
        return match ($this) {
            self::ALIMENTAIRE => 'Alimentaire',
            self::BOISSONS => 'Boissons',
            self::HYGIENE => 'Hygiène',
            self::ENTRETIEN => 'Entretien',
            self::AUTRE => 'Autre',
        };
    }
}
