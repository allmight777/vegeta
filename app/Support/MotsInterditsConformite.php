<?php

namespace App\Support;

/**
 * Source unique des mots que le rôle caissier ne doit jamais voir (Loi art. 63 —
 * CLAUDE.md §3). Utilisée par NonDivulgationCaissierTest et par
 * Services\Assistance\FiltreConformiteReponseIa : une seule liste, jamais dupliquée
 * (08_PROMPT_ASSISTANT_IA_CONFORMITE §3.1).
 *
 * Recherche par mot entier (limites `\b`), pas par sous-chaîne : une correspondance
 * naïve sur "dos" matcherait "dossier" sur toutes les pages de l'application, et "ppe"
 * matcherait des mots anglais courants ("wrapper", "uppercase") — deux faux positifs
 * déjà rencontrés dans ce dépôt (07_PROMPT_MODE_DEGRADE_NPI_OCR §0).
 */
class MotsInterditsConformite
{
    public const LISTE = [
        'soupçon', 'soupcon', 'PPE', 'sanction', 'gel', 'DOS', 'financement du terrorisme',
    ];

    public static function contient(string $texte): ?string
    {
        foreach (self::LISTE as $mot) {
            $motif = '/\b'.preg_quote($mot, '/').'\b/iu';

            if (preg_match($motif, $texte) === 1) {
                return $mot;
            }
        }

        return null;
    }
}
