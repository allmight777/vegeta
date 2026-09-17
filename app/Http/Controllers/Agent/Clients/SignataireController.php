<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Enums\StatutFileAttenteNpi;
use App\Enums\StatutFiltrage;
use App\Enums\StatutVerificationNpi;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Clients\SignataireRequest;
use App\Models\PersonneMorale;
use App\Models\Signataire;
use App\Models\VerificationNpiEnAttente;
use App\Services\Audit\Consignateur;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Kyc\ResolveurStatutNpi;
use Illuminate\Http\RedirectResponse;

class SignataireController extends Controller
{
    public function stocker(SignataireRequest $request, PersonneMorale $personneMorale, MoteurFiltrage $moteurFiltrage, ResolveurStatutNpi $resolveurStatutNpi): RedirectResponse
    {
        if ($personneMorale->signataires()->count() >= 3) {
            return redirect()->route('agent.clients.completer', $personneMorale->client_id)
                ->withErrors('Une personne morale ne peut avoir plus de 3 signataires.');
        }

        $donnees = $request->validated();

        $signataire = Signataire::create(array_merge(
            array_diff_key($donnees, array_flip(['npi'])),
            [
                'personne_morale_id' => $personneMorale->id,
                'statut_filtrage' => StatutFiltrage::AVerifier,
            ],
        ));

        $agentId = auth('agent')->id();

        if (filled($donnees['npi'] ?? null)) {
            $npi = $donnees['npi'];
            ['statut' => $statut, 'resultat' => $resultat] = $resolveurStatutNpi->resoudre($npi);

            $signataire->definirNpi($npi);

            if ($statut === StatutVerificationNpi::EnAttenteConnexion) {
                $signataire->saveQuietly();
                $personneMorale->client()->update(['statut_verification_npi' => $statut]);

                VerificationNpiEnAttente::create([
                    'client_id' => $personneMorale->client_id,
                    'signataire_id' => $signataire->id,
                    'npi_idx' => $signataire->npi_idx,
                    'npi_chiffre' => $npi,
                    'statut' => StatutFileAttenteNpi::EnAttente,
                ]);

                Consignateur::enregistrer('agent', $agentId, 'npi_en_attente_connexion', 'client', $personneMorale->client_id);
            } else {
                $signataire->npi_verifie_le = $resultat?->verifieLe;
                $signataire->npi_verification_source = $resultat?->source;
                $signataire->saveQuietly();
            }
        }

        // Chaque signataire, mandataire et bénéficiaire effectif est contrôlé au même
        // titre que le client principal, avant toute validation de la fiche (§5.4).
        $moteurFiltrage->filtrer($signataire->fresh());

        Consignateur::enregistrer('agent', $agentId, 'ajout_signataire', 'signataire', $signataire->id);

        return redirect()->route('agent.clients.completer', $personneMorale->client_id)
            ->with('statut', 'Signataire ajouté — à vérifier par la conformité.');
    }

    public function detruire(Signataire $signataire): RedirectResponse
    {
        $clientId = $signataire->personneMorale->client_id;
        Consignateur::enregistrer('agent', auth('agent')->id(), 'suppression_signataire', 'signataire', $signataire->id);
        $signataire->delete();

        return redirect()->route('agent.clients.completer', $clientId)->with('statut', 'Signataire retiré.');
    }
}
