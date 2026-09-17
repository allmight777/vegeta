<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Contracts\ConnecteurSystemeExistant;
use App\Http\Controllers\Controller;
use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Services\Audit\Consignateur;
use App\Services\Kyc\CalculateurCompletude;
use App\Services\Kyc\ReferentielFicheAdhesion;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Complétion depuis le système existant du SFD (06_PROMPT §7) : recherche un client déjà
 * présent (import CSV core banking ou API), affiche une comparaison champ par champ, et
 * ne modifie que les champs explicitement cochés par l'agent.
 */
class SystemeExistantController extends Controller
{
    public function rechercher(Client $client, ConnecteurSystemeExistant $connecteur, ReferentielFicheAdhesion $referentiel): View
    {
        $client->load('personnePhysique');
        $personne = $client->personnePhysique;

        $trouve = $personne ? $connecteur->rechercherParCritere('personne_physique', [
            'npi' => null,
            'nom' => (string) $personne->nom,
            'prenoms' => (string) $personne->prenoms,
            'date_naissance' => $personne->date_naissance,
            'exclure_client_id' => $client->id,
        ]) : null;

        $comparaison = [];
        if ($trouve !== null) {
            foreach ($trouve as $code => $valeurTrouvee) {
                $valeurActuelle = $personne?->{$code};
                if (blank($valeurActuelle) || (string) $valeurActuelle !== (string) $valeurTrouvee) {
                    $comparaison[$code] = [
                        'libelle' => $referentiel->champ('personne_physique', $code)['libelle'] ?? $code,
                        'actuelle' => $valeurActuelle,
                        'trouvee' => $valeurTrouvee,
                        'vide' => blank($valeurActuelle),
                    ];
                }
            }
        }

        return view('agent.clients.systeme-existant', [
            'client' => $client,
            'trouve' => $trouve !== null,
            'comparaison' => $comparaison,
        ]);
    }

    public function appliquer(Request $request, Client $client, CalculateurCompletude $completude): RedirectResponse
    {
        $client->load('personnePhysique');
        $personne = $client->personnePhysique;
        $champsCoches = array_keys($request->input('champs', []));
        $champsAppliques = [];

        foreach ($champsCoches as $code) {
            if (! array_key_exists($code, PersonnePhysique::CHAMPS_COMPLETABLES)) {
                continue;
            }

            $valeur = $request->input("valeurs.{$code}");

            // Un champ déjà rempli n'est jamais écrasé sans que l'agent l'ait
            // explicitement coché lui-même — la case à cocher est ce consentement.
            $personne?->fill([$code => $valeur]);
            $champsAppliques[] = $code;
        }

        $personne?->save();
        $completude->evaluer($client->fresh(['personnePhysique']));

        Consignateur::enregistrer(
            'agent',
            auth('agent')->id(),
            'completion_systeme_existant:champs='.implode(',', $champsAppliques),
            'client',
            $client->id,
        );

        return redirect()->route('agent.clients.completer', $client)
            ->with('statut', count($champsAppliques).' champ(s) complété(s) depuis le système existant.');
    }
}
