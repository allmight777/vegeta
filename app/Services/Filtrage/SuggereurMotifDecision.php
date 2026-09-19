<?php

namespace App\Services\Filtrage;

use App\Enums\MotifDecisionFiltrage;
use App\Models\Agent;
use App\Models\Client;
use App\Models\EntreeListe;
use App\Models\Signataire;
use App\Services\Assistance\SelecteurProviderIa;
use Illuminate\Support\Str;

/**
 * Amélioration ergonomique, jamais démo-critique (12_PROMPT_IA_INTEGREE_PROFONDE §6,
 * point 2) : suggère le motif codé le plus probable à partir du texte libre que le
 * responsable vient de taper pour "Autre", avant tout enregistrement. Le responsable
 * garde toujours le dernier mot — aucune suggestion n'est jamais appliquée seule.
 *
 * Ne part jamais de `motif_detail` déjà enregistré (chiffré, potentiellement porteur
 * d'un nom tapé à la main) : uniquement le texte éphémère de la requête en cours.
 * Hors connexion ou sans clé API, `SelecteurProviderIa` retombe sur le simulateur par
 * mots-clés, qui ne trouvera rien dans la base de connaissances pour ce texte — la
 * méthode retourne alors simplement `null`, sans erreur ni dépendance au réseau.
 */
class SuggereurMotifDecision
{
    public function __construct(private readonly SelecteurProviderIa $selecteurProviderIa) {}

    public function suggerer(string $texteLibre, Client|Signataire $cible, EntreeListe $entree, Agent $agent): ?MotifDecisionFiltrage
    {
        $texte = trim($texteLibre);

        if (mb_strlen($texte) < 10 || $this->mentionneUneIdentite($texte, $cible, $entree)) {
            return null;
        }

        $reponse = $this->selecteurProviderIa->choisir()->repondre($this->question($texte), [], [], $agent);

        return $this->interpreter($reponse);
    }

    /**
     * Aucun nom (client ou personne listée) ne doit atteindre le fournisseur externe,
     * même reformulé dans un motif libre — comparaison par mot (≥ 3 lettres, normalisé
     * comme `GenerateurEmpreinte::normaliser`), pas par sous-chaîne exacte.
     */
    private function mentionneUneIdentite(string $texte, Client|Signataire $cible, EntreeListe $entree): bool
    {
        $normaliser = fn (string $valeur) => Str::of($valeur)->ascii()->upper()->squish()->toString();
        $texteNormalise = $normaliser($texte);

        $noms = array_filter([
            $entree->nom,
            $cible instanceof Signataire ? $cible->nom : $cible->nomAffichage(),
        ]);

        foreach ($noms as $nom) {
            foreach (preg_split('/\s+/', $normaliser($nom)) as $mot) {
                if (mb_strlen($mot) >= 3 && str_contains($texteNormalise, $mot)) {
                    return true;
                }
            }
        }

        return false;
    }

    private function question(string $texte): string
    {
        $options = collect(MotifDecisionFiltrage::cases())
            ->reject(fn (MotifDecisionFiltrage $m) => $m === MotifDecisionFiltrage::Autre)
            ->map(fn (MotifDecisionFiltrage $m) => "- {$m->value} : {$m->libelle()}")
            ->implode("\n");

        return 'Un responsable de conformité vient de taper ce motif libre pour écarter une '.
            "correspondance de filtrage sanctions/PPE : \"{$texte}\"\n\n".
            'Réponds UNIQUEMENT par un des codes suivants, exactement tel quel et sans phrase '.
            "autour, ou par \"aucun\" si rien ne correspond clairement :\n{$options}";
    }

    private function interpreter(string $reponse): ?MotifDecisionFiltrage
    {
        $normalisee = trim(mb_strtolower($reponse));

        foreach (MotifDecisionFiltrage::cases() as $motif) {
            if ($motif !== MotifDecisionFiltrage::Autre && str_contains($normalisee, $motif->value)) {
                return $motif;
            }
        }

        return null;
    }
}
