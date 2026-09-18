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
    case PlafondQuotidienApproche = 'plafond_quotidien_approche';
    case PlafondQuotidienDepasse = 'plafond_quotidien_depasse';
    case NpiInvalideApresVerification = 'npi_invalide_apres_verification';

    public function libelle(): string
    {
        return match ($this) {
            self::FiltrageSanction => 'Correspondance liste de sanctions',
            self::FiltragePpe => 'Correspondance PPE',
            self::FractionnementGuichet => 'Fractionnement au guichet',
            self::FractionnementMultiAgences => 'Fractionnement multi-agences',
            self::SeuilMensuelCentif => 'Seuil mensuel CENTIF dépassé',
            self::CompteDormantReactive => 'Compte dormant réactivé',
            self::PlafondQuotidienApproche => 'Plafond quotidien espèces bientôt atteint',
            self::PlafondQuotidienDepasse => 'Plafond quotidien espèces dépassé (tous comptes)',
            self::NpiInvalideApresVerification => 'NPI invalide après vérification différée',
        };
    }
}
