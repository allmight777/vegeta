<?php

namespace App\Services\Assistance;

use App\Models\Admin;
use App\Models\Agence;
use App\Models\DocumentIa;
use App\Services\Assistance\Outils\OutilCompterAlertesDuJour;
use App\Services\Assistance\Outils\OutilCompterProfilsIncomplets;
use App\Services\Assistance\Outils\OutilStatistiquesAgregeesAgence;

/**
 * « Résumé du jour » du tableau de bord admin (16_PROMPT §4.1). Compose deux ou trois
 * phrases lisibles à partir des résultats STRUCTURÉS des outils d'agrégation de l'assistant
 * (les mêmes que le widget de chat, jamais un texte généré) : aucune IA, aucun appel réseau,
 * donc identique en mode simulateur hors connexion. Uniquement des comptages : aucune
 * identité, aucun nom de dossier.
 *
 * Périmètre : les agences visibles de l'admin (son réseau, ou toutes pour l'admin plateforme),
 * revalidées par les outils eux-mêmes (ResoutAgenceOutil).
 */
class ResumeDuJourAdmin
{
    public function __construct(
        private readonly OutilStatistiquesAgregeesAgence $statistiques,
        private readonly OutilCompterAlertesDuJour $alertes,
        private readonly OutilCompterProfilsIncomplets $profils,
    ) {}

    /**
     * @return array<int, array{texte: string, lien: ?string, libelle_lien: ?string}>
     */
    public function composer(Admin $admin): array
    {
        $agences = Agence::query()
            ->when(! $admin->estAdminPlateforme(), fn ($q) => $q->where('reseau_id', $admin->reseau_id))
            ->orderBy('nom')
            ->get();

        $dossiers = 0;
        $completudePonderee = 0.0;
        $incomplets = 0;
        $plusDe30Jours = 0;
        $alertes = ['critique' => 0, 'attention' => 0, 'info' => 0];
        $agenceLaPlusChargee = null;
        $maxCritiques = 0;

        foreach ($agences as $agence) {
            $arguments = ['agence_id' => $agence->id];

            $stats = $this->statistiques->executer($arguments, $admin);
            $profils = $this->profils->executer($arguments, $admin);
            $alertesAgence = $this->alertes->executer($arguments, $admin);

            if (isset($stats['erreur']) || isset($profils['erreur']) || isset($alertesAgence['erreur'])) {
                continue;
            }

            $dossiers += $stats['nombre_dossiers'];
            $completudePonderee += $stats['taux_completude_moyen'] * $stats['nombre_dossiers'];
            $incomplets += $profils['nombre_total'];
            $plusDe30Jours += $profils['repartition_anciennete']['plus_30_jours'] ?? 0;

            foreach (['critique', 'attention', 'info'] as $gravite) {
                $alertes[$gravite] += $alertesAgence[$gravite];
            }

            if ($alertesAgence['critique'] > $maxCritiques) {
                $maxCritiques = $alertesAgence['critique'];
                $agenceLaPlusChargee = $agence->nom;
            }
        }

        if ($dossiers === 0) {
            return [[
                'texte' => 'Aucun dossier client n\'est encore enregistré dans votre périmètre : le résumé se remplira dès les premières saisies.',
                'lien' => null,
                'libelle_lien' => null,
            ]];
        }

        $moyenne = round($completudePonderee / $dossiers, 1);
        $lignes = [];

        $texte = $this->pluriel($dossiers, 'dossier suivi', 'dossiers suivis').' dans '.$this->pluriel($agences->count(), 'agence', 'agences')
            .' — complétude KYC moyenne de '.number_format($moyenne, 1, ',', ' ').' %. ';
        $texte .= $incomplets === 0
            ? 'Aucun profil incomplet.'
            : $this->pluriel($incomplets, 'profil reste incomplet', 'profils restent incomplets')
                .($plusDe30Jours > 0 ? ", dont {$plusDe30Jours} depuis plus de 30 jours." : '.');
        $lignes[] = ['texte' => $texte, 'lien' => route('admin.agents.index'), 'libelle_lien' => 'Voir les agents'];

        $totalAlertes = array_sum($alertes);
        $texte = $totalAlertes === 0
            ? 'Aucune alerte ouverte pour le moment.'
            : $this->pluriel($totalAlertes, 'alerte ouverte', 'alertes ouvertes')
                ." ({$alertes['critique']} critique".($alertes['critique'] > 1 ? 's' : '')
                .", {$alertes['attention']} attention, {$alertes['info']} info)"
                .($agenceLaPlusChargee !== null ? ' — la plus exposée : '.$agenceLaPlusChargee.'.' : '.');
        $lignes[] = ['texte' => $texte, 'lien' => route('admin.regles-detection.index'), 'libelle_lien' => 'Voir les règles de détection'];

        $nbDocuments = DocumentIa::count();
        if ($nbDocuments > 0) {
            $utilisations = (int) DocumentIa::sum('nombre_utilisations');
            $lignes[] = [
                'texte' => $this->pluriel($nbDocuments, 'document chargé', 'documents chargés').' dans la bibliothèque — '
                    .($utilisations > 0 ? "déjà utilisés {$utilisations} fois pour répondre aux questions." : 'pas encore utilisés pour répondre.'),
                'lien' => route('admin.documents-ia.index'),
                'libelle_lien' => 'Voir la bibliothèque',
            ];
        }

        return $lignes;
    }

    private function pluriel(int $nombre, string $singulier, string $pluriel): string
    {
        return $nombre.' '.($nombre > 1 ? $pluriel : $singulier);
    }
}
