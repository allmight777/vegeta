<?php

namespace App\Http\Controllers\Agent\Identites;

use App\Enums\ModePaiement;
use App\Enums\StatutAlerte;
use App\Http\Controllers\Controller;
use App\Models\Alerte;
use App\Models\Compte;
use App\Models\CumulJournalier;
use App\Models\Identite;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use Illuminate\View\View;

/**
 * Vue consolidée d'une personne physique : tous ses comptes, toutes agences confondues,
 * son cumul d'espèces du jour face à son plafond, et la justification de chaque
 * rattachement. Réservée au responsable LBC/FT et à la direction (Loi art. 63).
 */
class IdentiteController extends Controller
{
    public function afficher(Identite $identite, ContexteReseau $contexte): View
    {
        $agent = auth('agent')->user();

        abort_unless(in_array($agent->role->value, ['responsable_lbcft', 'direction'], true), 403);
        abort_unless($identite->reseau_id === $contexte->reseauId(), 404);

        $clientIds = $identite->clientIds();

        $cumul = CumulJournalier::where('identite_id', $identite->id)
            ->where('mode_paiement', ModePaiement::Especes)
            ->whereDate('jour', today())
            ->first();

        // Toute consultation d'une vue consolidée est tracée : c'est une donnée sensible.
        Consignateur::enregistrer('agent', $agent->id, 'consultation_identite', 'identite', $identite->id);

        return view('agent.identites.afficher', [
            'identite' => $identite,
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
