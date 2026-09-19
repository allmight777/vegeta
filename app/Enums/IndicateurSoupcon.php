<?php

namespace App\Enums;

/**
 * Les indicateurs de soupçon de la fiche d'analyse FECECAM (section 3, « Analyse du caractère
 * suspect »), rien d'autre : chaque case de la fiche papier est un cas, aucun n'est inventé.
 */
enum IndicateurSoupcon: string
{
    case IncoherenceProfilOperations = 'incoherence_profil_operations';
    case DepotsFractionnesInhabituels = 'depots_fractionnes_inhabituels';
    case TransactionsPaysARisque = 'transactions_pays_a_risque';
    case ComportementSuspectEvasif = 'comportement_suspect_evasif';
    case RefusDocumentsRequis = 'refus_documents_requis';
    case RefusSourcesRevenus = 'refus_sources_revenus';
    case Autres = 'autres';

    public function libelle(): string
    {
        return match ($this) {
            self::IncoherenceProfilOperations => 'Incohérence entre le profil et les opérations',
            self::DepotsFractionnesInhabituels => 'Dépôts fractionnés ou inhabituels',
            self::TransactionsPaysARisque => 'Transactions avec des pays à risque',
            self::ComportementSuspectEvasif => 'Comportement du client suspect ou évasif',
            self::RefusDocumentsRequis => 'Refus de fournir les documents requis',
            self::RefusSourcesRevenus => 'Refus de fournir la ou les sources de revenus',
            self::Autres => 'Autres (à préciser)',
        };
    }

    /** Indicateurs que le système sait détecter seul ; les autres relèvent du jugement du contrôleur. */
    public function estDetectableAutomatiquement(): bool
    {
        return in_array($this, [
            self::IncoherenceProfilOperations,
            self::DepotsFractionnesInhabituels,
            self::RefusDocumentsRequis,
            self::RefusSourcesRevenus,
        ], true);
    }
}
