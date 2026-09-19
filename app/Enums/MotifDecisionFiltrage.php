<?php

namespace App\Enums;

/**
 * Motifs codés plutôt que texte libre : c'est ce qui rend une décision réutilisable
 * par le moteur et mesurable dans le temps (arbre de justification narrative).
 * Le texte d'audit est assemblé à partir du code, l'agent n'a plus à le rédiger.
 */
enum MotifDecisionFiltrage: string
{
    case HomonymeDateNaissance = 'homonyme_date_naissance';
    case HomonymeNationalite = 'homonyme_nationalite';
    case HomonymeSimple = 'homonyme_simple';
    case ActiviteLegitime = 'activite_legitime';
    case PieceIdentiteVerifiee = 'piece_identite_verifiee';
    case CorrespondanceConfirmee = 'correspondance_confirmee';
    case Autre = 'autre';

    public function libelle(): string
    {
        return match ($this) {
            self::HomonymeDateNaissance => 'Homonyme — date de naissance différente',
            self::HomonymeNationalite => 'Homonyme — nationalité différente',
            self::HomonymeSimple => 'Homonyme — nom courant, aucun autre élément concordant',
            self::ActiviteLegitime => 'Activité déclarée cohérente et vérifiée',
            self::PieceIdentiteVerifiee => "Pièce d'identité contrôlée en agence",
            self::CorrespondanceConfirmee => 'Correspondance confirmée avec la personne listée',
            self::Autre => 'Autre motif (à préciser)',
        };
    }

    /** Phrase d'audit assemblée automatiquement, reprise dans le journal et les rapports. */
    public function phraseAudit(string $nomListeMasque, string $scoreFormate): string
    {
        return match ($this) {
            self::HomonymeDateNaissance => "Correspondance écartée avec {$nomListeMasque} (similarité {$scoreFormate}) : la date de naissance du membre diffère de celle de la personne listée.",
            self::HomonymeNationalite => "Correspondance écartée avec {$nomListeMasque} (similarité {$scoreFormate}) : la nationalité du membre diffère de celle de la personne listée.",
            self::HomonymeSimple => "Correspondance écartée avec {$nomListeMasque} (similarité {$scoreFormate}) : homonymie sur un patronyme courant, aucun autre élément d'identification concordant.",
            self::ActiviteLegitime => "Correspondance écartée avec {$nomListeMasque} (similarité {$scoreFormate}) : activité déclarée du membre vérifiée et cohérente avec les flux observés.",
            self::PieceIdentiteVerifiee => "Correspondance écartée avec {$nomListeMasque} (similarité {$scoreFormate}) : pièce d'identité du membre contrôlée physiquement en agence.",
            self::CorrespondanceConfirmee => "Correspondance confirmée avec {$nomListeMasque} (similarité {$scoreFormate}) : vigilance renforcée appliquée au dossier.",
            self::Autre => "Décision motivée sur {$nomListeMasque} (similarité {$scoreFormate}) : voir le détail saisi par le responsable.",
        };
    }

    public function estEcart(): bool
    {
        return $this !== self::CorrespondanceConfirmee;
    }
}
