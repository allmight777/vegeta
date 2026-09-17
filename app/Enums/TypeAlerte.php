<?php

namespace App\Enums;

enum TypeAlerte: string
{
    case FiltrageSanction = 'filtrage_sanction';
    case FiltragePpe = 'filtrage_ppe';
    case FractionnementGuichet = 'fractionnement_guichet';
    case FractionnementMultiAgences = 'fractionnement_multi_agences';
    case SeuilMensuelCentif = 'seuil_mensuel_centif';
    case CompteDormantReactive = 'compte_dormant_reactive';

    public function libelle(): string
    {
        return match ($this) {
            self::FiltrageSanction => 'Correspondance liste de sanctions',
            self::FiltragePpe => 'Correspondance PPE',
            self::FractionnementGuichet => 'Fractionnement au guichet',
            self::FractionnementMultiAgences => 'Fractionnement multi-agences',
            self::SeuilMensuelCentif => 'Seuil mensuel CENTIF dépassé',
            self::CompteDormantReactive => 'Compte dormant réactivé',
        };
    }
}
