<?php

namespace App\Enums;

enum RecuStatus: string
{
    case EN_ATTENTE = 'en_attente';
    case TRAITE = 'traite';
    case ECHOUE = 'echoue';

    public function label(): string
    {
        return match ($this) {
            self::EN_ATTENTE => 'En attente',
            self::TRAITE => 'Traité',
            self::ECHOUE => 'Échoué',
        };
    }
}
