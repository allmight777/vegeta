<?php

namespace App\Http\Controllers\Agent\Clients;

use App\Enums\SourceCreation;
use App\Enums\StatutFileAttenteNpi;
use App\Enums\StatutVerificationNpi;
use App\Enums\TypeClient;
use App\Http\Controllers\Controller;
use App\Http\Requests\Agent\Clients\CompleterClientRequest;
use App\Http\Requests\Agent\Clients\CreerClientRequest;
use App\Models\Client;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Models\VerificationNpiEnAttente;
use App\Services\Audit\Consignateur;
use App\Services\Contexte\ContexteReseau;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Kyc\CalculateurCompletude;
use App\Services\Kyc\CreateurClient;
use App\Services\Kyc\ResolveurStatutNpi;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\View\View;

class ClientController extends Controller
{
    public function index(Request $request, ContexteReseau $contexte): View
    {
        $clients = Client::with(['personnePhysique', 'personneMorale'])
            ->where('reseau_id', $contexte->reseauId())
            ->when($request->boolean('a_completer'), fn ($q) => $q->where('score_completude_kyc', '<', 100))
            ->orderBy('score_completude_kyc')
            ->paginate(20);

        return view('agent.clients.index', ['clients' => $clients]);
    }

    public function creer(): View
    {
        return view('agent.clients.creer');
    }

    public function stocker(CreerClientRequest $request, ContexteReseau $contexte, CreateurClient $createurClient): RedirectResponse
    {
        $client = $createurClient->creer(
            $request->validated(),
            $contexte->reseauId(),
            SourceCreation::SaisieAgent,
            $request->user('agent'),
        );

        $message = $client->statut_verification_npi === StatutVerificationNpi::EnAttenteConnexion
            ? 'Client créé — le NPI sera vérifié automatiquement dès le retour de la connexion.'
            : 'Client créé. Complétez sa fiche.';

        return redirect()->route('agent.clients.completer', $client)->with('statut', $message);
    }

    public function completer(Client $client): View
    {
        $client->load(['personnePhysique.mandataires', 'personneMorale.signataires', 'comptes']);
        $type = $client->type->value;
        $agent = auth('agent')->user();
        $personne = $client->type === TypeClient::PersonneMorale ? $client->personneMorale : $client->personnePhysique;

        $donnees = [
            'client' => $client,
            'type' => $type,
            'valeurs' => $this->extraireValeurs($personne),
            'champsManquants' => $personne?->champs_manquants ?? [],
        ];

        if ($agent?->estResponsableLbcft()) {
            if ($type === 'personne_morale') {
                $donnees['signatairesExistants'] = $client->personneMorale?->signataires;
                $donnees['fichesRlbcftSignataires'] = $client->personneMorale?->signataires
                    ->mapWithKeys(fn ($s) => [$s->id => $s->ficheRlbcft?->only(['ppe_national', 'ppe_etranger', 'sanction_financiere_internationale', 'financement_terrorisme', 'visa_rlbcft_nom', 'visa_rlbcft_date']) ?? []])
                    ->all() ?? [];
            } else {
                $donnees['ficheRlbcft'] = $client->ficheRlbcft?->only(['ppe_national', 'ppe_etranger', 'sanction_financiere_internationale', 'financement_terrorisme', 'visa_rlbcft_nom', 'visa_rlbcft_date']) ?? [];
            }
        } elseif ($type === 'personne_morale') {
            $donnees['signatairesExistants'] = $client->personneMorale?->signataires;
        }

        return view('agent.clients.completer', $donnees);
    }

    public function mettreAJour(CompleterClientRequest $request, Client $client, CalculateurCompletude $completude, MoteurFiltrage $moteurFiltrage): RedirectResponse
    {
        $donnees = $request->validated();

        if ($client->type === TypeClient::PersonnePhysique) {
            $personne = $client->personnePhysique;
            $personne?->fill(array_intersect_key($donnees, PersonnePhysique::CHAMPS_COMPLETABLES))->save();

            if (filled($donnees['npi'] ?? null)) {
                $this->appliquerNpiSurPersonne($personne, $client, $donnees['npi']);
            }

            if (auth('agent')->user()?->estResponsableLbcft() && ($donnees['fiche_rlbcft'] ?? null)) {
                $client->ficheRlbcft()->updateOrCreate([], $donnees['fiche_rlbcft']);
            }
        } else {
            $champs = array_intersect_key($donnees, array_flip([
                'raison_sociale', 'forme_juridique', 'date_creation', 'adresse', 'rccm', 'ifu',
                'telephone', 'email', 'activite_1', 'activite_2', 'revenus_mensuels_estimes',
                'beneficiaire_effectif_texte', 'beneficiaire_effectif_signataire_id',
                'droit_adhesion', 'part_sociale', 'depot_especes',
                'signature_representants_path', 'signature_responsable_nom',
                'signature_responsable_fonction', 'signature_responsable_date',
            ]));
            $client->personneMorale?->fill($champs)->save();

            if (auth('agent')->user()?->estResponsableLbcft() && ($donnees['fiche_rlbcft'] ?? null)) {
                foreach ($donnees['fiche_rlbcft'] as $signataireId => $champsFiche) {
                    $signataire = $client->personneMorale?->signataires->firstWhere('id', $signataireId);
                    $signataire?->ficheRlbcft()->updateOrCreate([], $champsFiche);
                }
            }
        }

        $moteurFiltrage->filtrer($client->fresh());
        $resultat = $completude->evaluer($client->fresh(['personnePhysique', 'personneMorale']));

        Consignateur::enregistrer('agent', auth('agent')->id(), 'mise_a_jour_kyc', 'client', $client->id);

        return redirect()->route('agent.clients.completer', $client)
            ->with('statut', "Fiche mise à jour — complétude {$resultat['score']} %.");
    }

    /**
     * @return array<string, mixed>
     */
    private function extraireValeurs(PersonnePhysique|PersonneMorale|null $personne): array
    {
        if ($personne === null) {
            return [];
        }

        $codes = $personne instanceof PersonnePhysique
            ? array_keys(PersonnePhysique::CHAMPS_COMPLETABLES)
            : ['raison_sociale', 'forme_juridique', 'date_creation', 'adresse', 'rccm', 'ifu', 'telephone', 'email', 'activite_1', 'activite_2', 'revenus_mensuels_estimes', 'beneficiaire_effectif_texte', 'beneficiaire_effectif_signataire_id', 'droit_adhesion', 'part_sociale', 'depot_especes', 'total_versements_initiaux', 'signature_representants_path', 'signature_responsable_nom', 'signature_responsable_fonction', 'signature_responsable_date'];

        $valeurs = collect($codes)->mapWithKeys(fn (string $code) => [$code => $personne->{$code}])->all();

        // Les dates chiffrées (personne physique) sont des chaînes libres AAAA-MM-JJ ;
        // les colonnes castées 'date' exposent un Carbon à formater pour un <input date>.
        foreach ($valeurs as $code => $valeur) {
            if ($valeur instanceof Carbon) {
                $valeurs[$code] = $valeur->format('Y-m-d');
            }
        }

        return $valeurs;
    }

    private function appliquerNpiSurPersonne(PersonnePhysique $personne, Client $client, string $npi): void
    {
        ['statut' => $statut, 'resultat' => $resultat] = app(ResolveurStatutNpi::class)->resoudre($npi);

        $personne->definirNpi($npi);

        if ($statut === StatutVerificationNpi::EnAttenteConnexion) {
            $personne->saveQuietly();
            $client->update(['statut_verification_npi' => $statut]);

            VerificationNpiEnAttente::create([
                'client_id' => $client->id,
                'npi_idx' => $personne->npi_idx,
                'npi_chiffre' => $npi,
                'statut' => StatutFileAttenteNpi::EnAttente,
            ]);

            Consignateur::enregistrer('agent', auth('agent')->id(), 'npi_en_attente_connexion', 'client', $client->id);

            return;
        }

        $personne->npi_verifie_le = $resultat?->verifieLe;
        $personne->npi_verification_source = $resultat?->source;
        $personne->saveQuietly();

        $client->update([
            'statut_verification_npi' => $statut,
            'npi_verifie_le' => $resultat?->verifieLe,
        ]);
    }
}
