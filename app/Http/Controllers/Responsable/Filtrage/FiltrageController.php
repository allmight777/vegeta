<?php

namespace App\Http\Controllers\Responsable\Filtrage;

use App\Enums\MotifDecisionFiltrage;
use App\Enums\StatutAlerte;
use App\Enums\StatutFiltrage;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Filtrage\DeciderRequest;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\DecisionFiltrage;
use App\Models\ResultatFiltrage;
use App\Models\Signataire;
use App\Services\Audit\Consignateur;
use App\Services\Filtrage\MemoireDecisions;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;

class FiltrageController extends Controller
{
    public function __construct(private readonly MemoireDecisions $memoire) {}

    public function index(): View
    {
        $agenceId = Auth::guard('agent')->user()->agence_id;
        $clientIdsDeLAgence = Client::deLAgence($agenceId)->pluck('id')->all();

        $resultats = ResultatFiltrage::with([
            'entreeListe',
            'filtrable' => fn ($morphTo) => $morphTo->morphWith([
                Client::class => ['personnePhysique', 'personneMorale'],
                Signataire::class => ['personneMorale.client'],
            ]),
        ])
            ->where('statut', StatutFiltrage::AVerifier)
            ->get()
            ->filter(function ($resultat) use ($clientIdsDeLAgence) {
                $client = $this->clientDe($resultat->filtrable);

                return $client !== null && in_array($client->id, $clientIdsDeLAgence, true);
            })
            ->sortByDesc('score_similarite')
            ->values();

        return view('responsable.filtrage.index', [
            'resultats' => $resultats,
            'motifs' => MotifDecisionFiltrage::cases(),
            // Mesure du bruit évité : c'est ce chiffre qui montre que l'outil reste
            // utilisable dans la durée, au lieu de noyer le responsable.
            'decisionsConnues' => DecisionFiltrage::count(),
            'alertesEvitees' => (int) DecisionFiltrage::sum('applications'),
        ]);
    }

    public function decider(DeciderRequest $request, ResultatFiltrage $resultatFiltrage): RedirectResponse
    {
        $donnees = $request->validated();
        $agent = auth('agent')->user();
        $statut = StatutFiltrage::from($donnees['statut']);
        $motif = MotifDecisionFiltrage::from($donnees['motif_code']);

        $cible = $resultatFiltrage->filtrable;

        // Phrase d'audit assemblée à partir du motif codé : le responsable coche,
        // le texte réglementaire s'écrit tout seul et reste homogène d'un dossier
        // à l'autre. Le nom de la personne listée n'apparaît jamais en entier.
        $phraseAudit = $motif->phraseAudit(
            $this->masquer($resultatFiltrage->entreeListe->nom),
            number_format(((float) $resultatFiltrage->score_similarite) * 100, 0, ',', ' ').' %',
        );

        $resultatFiltrage->update([
            'statut' => $statut,
            'verifie_par_agent_id' => $agent->id,
            'verifie_le' => now(),
            'motif_ecart' => trim($phraseAudit.' '.($donnees['motif'] ?? '')),
        ]);

        if ($cible !== null) {
            $this->memoire->enregistrer($resultatFiltrage, $cible, $statut, $motif, $donnees['motif'] ?? null, $agent);
        }

        if ($cible instanceof Signataire) {
            $cible->update(['statut_filtrage' => $statut]);
        }
        if ($cible instanceof Client && $statut === StatutFiltrage::Confirme && $cible->statut_ppe->value === 'ppe_a_verifier') {
            $cible->update(['statut_ppe' => 'ppe_confirme']);
        }

        Alerte::where('resultat_filtrage_id', $resultatFiltrage->id)->update([
            'statut' => StatutAlerte::Traitee,
            'traitee_par_agent_id' => $agent->id,
            'traitee_le' => now(),
        ]);

        Consignateur::enregistrer('agent', $agent->id, 'decision_filtrage', 'resultat_filtrage', $resultatFiltrage->id);

        return redirect()->route('responsable.filtrage.index')
            ->with('statut', 'Décision enregistrée. Cette correspondance ne sera plus signalée pour cette personne.');
    }

    /** Initiales seules : on ne stigmatise pas une personne listée avant confirmation. */
    private function masquer(string $nom): string
    {
        return collect(preg_split('/\s+/', trim($nom)))
            ->map(fn (string $mot) => mb_substr($mot, 0, 1).'.')
            ->implode(' ');
    }

    private function clientDe(Client|Signataire|null $cible): ?Client
    {
        return match (true) {
            $cible instanceof Client => $cible,
            $cible instanceof Signataire => $cible->personneMorale?->client,
            default => null,
        };
    }
}