<?php

namespace App\Services\Coherence;

/**
 * Transforme un faisceau de constats chiffrés en un texte qu'un responsable
 * d'agence lit en quinze secondes, et en actions de remédiation KYC.
 *
 * DOCTRINE — l'IA ne note rien.
 *
 * Le score, les seuils et la décision d'alerter sont produits par des règles
 * déterministes (les Indicateurs), reproductibles et opposables : à la
 * question « pourquoi ce membre a-t-il été signalé ? », la réponse doit être
 * « parce que ses dépôts valent 14,2 fois son revenu déclaré et qu'il a fait 7
 * allers-retours en moins de 48 h », jamais « parce que le modèle l'a jugé
 * suspect ». Un signalement LBC/FT non explicable est inopposable en contrôle.
 *
 * L'IA n'intervient qu'en surcouche : raconter, et proposer les questions à
 * poser. Sans connexion, le gabarit local ci-dessous produit le même texte en
 * moins élégant — la détection, elle, n'a jamais dépendu d'elle.
 */
class NarrateurFaisceau
{
    public function raconter(FaisceauIndices $faisceau): string
    {
        $phrases = array_map(fn (Constat $c) => $c->libelle, $faisceau->constats());

        $entete = sprintf(
            '%d écarts concordants entre le profil déclaré et les opérations observées.',
            $faisceau->nombreConstats(),
        );

        return $entete.' '.implode(' ', $phrases)
            .' Pris isolément, chacun de ces constats peut s\'expliquer ; c\'est leur concordance qui justifie une revue.';
    }

    /**
     * Actions de remédiation KYC proposées au responsable.
     *
     * C'est ce qui distingue un outil qui accuse d'un outil qui fait avancer
     * le dossier : l'alerte ne dit pas seulement « ce membre est douteux »,
     * elle dit quoi lui demander. La mise à jour de la fiche qui en découle
     * EST la vigilance constante attendue par le régulateur.
     *
     * @return array<int, string>
     */
    public function actionsRemediation(FaisceauIndices $faisceau): array
    {
        $actions = [];

        foreach ($faisceau->constats() as $constat) {
            $actions = array_merge($actions, match ($constat->code) {
                'ecart_flux_revenus' => [
                    'Demander au membre l\'origine des fonds déposés sur la période.',
                    'Mettre à jour les revenus déclarés dans la fiche KYC si l\'activité a changé.',
                    'Réclamer un justificatif d\'activité (RCCM, attestation de marché, bulletins).',
                ],
                'compte_de_passage' => [
                    'Demander au membre pour qui sont effectués ces retraits immédiats.',
                    'Vérifier qu\'aucun tiers non déclaré n\'opère sur le compte (mandataire).',
                ],
                'incoherence_activite_canal' => [
                    'Faire préciser l\'activité réelle et corriger le champ « activité » de la fiche.',
                    'Vérifier si une activité secondaire non déclarée explique ces flux.',
                ],
                default => [],
            });
        }

        return array_values(array_unique($actions));
    }
}
