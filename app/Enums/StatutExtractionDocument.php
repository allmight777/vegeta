<?php

namespace App\Enums;

enum StatutExtractionDocument: string
{
    case EnAttente = 'en_attente';
    case EnCours = 'en_cours';
    case Reussie = 'reussie';
    case Echouee = 'echouee';

    public function libelle(): string
    {
        return match ($this) {
            self::EnAttente => 'En attente',
            self::EnCours => 'En cours',
            self::Reussie => 'Extraction réussie',
            self::Echouee => 'Extraction échouée',
        };
    }
}
