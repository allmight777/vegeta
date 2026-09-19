<?php

namespace App\Http\Controllers\Responsable\Identites;

use App\Enums\ModePaiement;
use App\Enums\StatutAlerte;
use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\CumulJournalier;
use App\Models\Identite;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use Illuminate\View\View;

/**
 * Vue consolidée d'une personne physique : tous ses comptes, toutes agences confondues,
 * son cumul d'espèces du jour face à son plafond, et la justification de chaque
 * rattachement. Réservée au responsable d'agence (Loi art. 63), et seulement pour une
 * identité ayant un pied dans son agence — la consolidation multi-agences reste visible
 * une fois l'accès accordé : c'est le but de cet écran (détection du fractionnement
 * inter-agences), cf. docs/DECISIONS.md.
 */
class IdentiteController extends Controller
{
    public function afficher(Identite $identite, ContexteReseau $contexte): View
    {
        $agent = auth('agent')->user();

        abort_unless($identite->reseau_id === $contexte->reseauId(), 404);

        $clientIds = $identite->clientIds();
        abort_unless(Client::deLAgence($agent->agence_id)->whereIn('id', $clientIds)->exists(), 404);

        $cumul = CumulJournalier::where('identite_id', $identite->id)
            ->where('mode_paiement', ModePaiement::Especes)
            ->whereDate('jour', today())
            ->first();

        // Toute consultation d'une vue consolidée est tracée : c'est une donnée sensible.
        Consignateur::enregistrer('agent', $agent->id, 'consultation_identite', 'identite', $identite->id);

        return view('responsable.identites.afficher', [
            'personneConsolidee' => $identite,
            'clients' => $identite->clients()->with(['personnePhysique', 'personneMorale'])->get(),
            'comptes' => Compte::with('agence')->whereIn('client_id', $clientIds)->get(),
            'rattachements' => $identite->rattachements()
                ->with(['client.personnePhysique', 'client.personneMorale'])
                ->latest()
                ->get(),
            'cumul' => $cumul,
            'pourcentagePlafond' => $this->pourcentage($cumul?->totalRetenu() ?? 0.0, (float) $identite->plafond_quotidien_especes),
            'alertes' => Alerte::whereIn('client_id', $clientIds)
                ->where('statut', '!=', StatutAlerte::Traitee)
                ->latest()
                ->get(),
        ]);
    }

    private function pourcentage(float $cumul, float $plafond): int
    {
        return $plafond <= 0 ? 0 : (int) min(100, round($cumul / $plafond * 100));
    }
}
