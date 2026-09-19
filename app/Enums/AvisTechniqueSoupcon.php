<?php

namespace App\Enums;

/** Avis technique de la fiche (sections « Décision et suites à donner » et « Validation »). */
enum AvisTechniqueSoupcon: string
{
    case DeclarationCentif = 'declaration_centif';
    case InvestigationAPoursuivre = 'investigation_a_poursuivre';
    case ClassementSansSuite = 'classement_sans_suite';

    public function libelle(): string
    {
        return match ($this) {
            self::DeclarationCentif => 'Déclaration à la CENTIF',
            self::InvestigationAPoursuivre => 'Enquêtes / investigations à poursuivre',
            self::ClassementSansSuite => 'Classement sans suite avec renforcement du suivi',
        };
    }
}
