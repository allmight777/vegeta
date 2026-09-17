<?php

namespace App\Services\Assistance;

use App\Models\Admin;
use App\Models\Agent;
use App\Services\Kyc\ReferentielFicheAdhesion;

/**
 * Construit le contexte envoyé à un fournisseur d'IA (réel ou simulateur) à partir d'une
 * LISTE FERMÉE (08_PROMPT_ASSISTANT_IA_CONFORMITE §3.2) : rôle (jamais le nom), écran,
 * référentiel de configuration, lexique produit/réglementaire, et — seulement si déjà
 * fournis par l'appelant après ses propres vérifications — les codes de champs manquants
 * et un texte d'explication d'alerte déjà généré par le système. Toute autre clé de
 * `$donneesEcran` est ignorée : cette liste ne doit jamais être élargie "au cas où" sans
 * revue de code explicite — c'est la seule garantie qu'aucune donnée d'identité
 * n'atteigne un fournisseur externe (CLAUDE.md §5).
 */
class ConstructeurContexteIa
{
    public function __construct(private readonly ReferentielFicheAdhesion $referentiel) {}

    /**
     * @param  array<string, mixed>  $donneesEcran
     * @return array<string, mixed>
     */
    public function construire(Agent|Admin $utilisateur, string $ecran, array $donneesEcran = []): array
    {
        $estGuichet = $utilisateur instanceof Agent && $utilisateur->estGuichet();

        $contexte = [
            'role' => $this->role($utilisateur),
            'ecran' => $ecran,
            'referentiel' => $this->referentielAplati(),
            'lexique' => $this->lexiqueReglementaire(),
        ];

        if (! empty($donneesEcran['champs_manquants']) && is_array($donneesEcran['champs_manquants'])) {
            $contexte['champs_manquants'] = array_values(array_map('strval', $donneesEcran['champs_manquants']));
        }

        if (! $estGuichet && filled($donneesEcran['explication_alerte'] ?? null)) {
            $contexte['explication_alerte'] = (string) $donneesEcran['explication_alerte'];
        }

        return $contexte;
    }

    private function role(Agent|Admin $utilisateur): string
    {
        if ($utilisateur instanceof Agent) {
            return $utilisateur->role->value;
        }

        return $utilisateur->estAdminPlateforme() ? 'admin_plateforme' : 'admin_reseau';
    }

    /**
     * @return array<string, string>
     */
    private function referentielAplati(): array
    {
        $libelles = [];

        foreach (['personne_physique', 'personne_morale'] as $type) {
            foreach ($this->referentiel->champsPlats($type) as $code => $definition) {
                $libelles[$code] = (string) $definition['libelle'];
            }
        }

        return $libelles;
    }

    /**
     * @return array<int, string>
     */
    private function lexiqueReglementaire(): array
    {
        return app(BaseConnaissances::class)
            ->charger()
            ->where('source', 'reglementaire')
            ->map(fn (array $entree) => $entree['question'].' : '.$entree['reponse'])
            ->values()
            ->all();
    }
}
