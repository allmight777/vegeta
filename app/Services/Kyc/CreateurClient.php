<?php

namespace App\Services\Kyc;

use App\Enums\SourceCreation;
use App\Enums\StatutFileAttenteNpi;
use App\Enums\StatutVerificationNpi;
use App\Enums\TypeClient;
use App\Models\Agent;
use App\Models\Client;
use App\Models\Mandataire;
use App\Models\PersonneMorale;
use App\Models\PersonnePhysique;
use App\Models\Signataire;
use App\Models\VerificationNpiEnAttente;
use App\Services\Audit\Consignateur;
use App\Services\Filtrage\MoteurFiltrage;
use App\Services\Identite\ResolveurIdentite;

/**
 * Factorise la création complète d'un client (personne physique ou morale, avec
 * mandataires/signataires et fiche RLBC/FT si autorisé) pour être appelée à l'identique
 * par la saisie manuelle (ClientController::stocker) et par la validation d'un document
 * importé (ImportDocumentController::valider) — 06_PROMPT §3.
 */
class CreateurClient
{
    public function __construct(
        private readonly CalculateurCompletude $completude,
        private readonly MoteurFiltrage $moteurFiltrage,
        private readonly ResolveurStatutNpi $resolveurStatutNpi,
        private readonly ResolveurIdentite $resolveurIdentite,
        private readonly DetecteurIncoherenceDepotSimule $detecteurIncoherenceDepotSimule,
    ) {}

    /**
     * @param  array<string, mixed>  $donnees  données déjà validées (FormRequest)
     */
    public function creer(array $donnees, int $reseauId, SourceCreation $source, ?Agent $agent): Client
    {
        $client = Client::create([
            'reseau_id' => $reseauId,
            'agence_creation_id' => $agent?->agence_id,
            'type' => $donnees['type'],
            'nature_relation' => $donnees['nature_relation'],
            'source_creation' => $source,
        ]);

        if ($donnees['type'] === TypeClient::PersonnePhysique->value) {
            $this->creerPersonnePhysique($client, $donnees, $agent);
        } else {
            $this->creerPersonneMorale($client, $donnees, $agent);
        }

        // Rattachement à la personne physique réelle : NPI d'abord, empreinte en secours.
        // Doit venir après appliquerNpi() (npi_idx écrit) et avant l'évaluation de
        // complétude, qui alimente le plafond quotidien de l'identité.
        $this->resolveurIdentite->rattacher($client->fresh('personnePhysique'), $agent);

        $this->completude->evaluer($client->fresh(['personnePhysique', 'personneMorale']));
        $this->moteurFiltrage->filtrer($client->fresh());
        $this->detecteurIncoherenceDepotSimule->verifier($client->fresh(['personnePhysique', 'personneMorale']), $agent);

        Consignateur::enregistrer('agent', $agent?->id, 'creation_client', 'client', $client->id);

        return $client->fresh(['personnePhysique.mandataires', 'personneMorale.signataires']);
    }

    private function creerPersonnePhysique(Client $client, array $donnees, ?Agent $agent): void
    {
        $champs = array_intersect_key($donnees, PersonnePhysique::CHAMPS_COMPLETABLES);
        $personne = PersonnePhysique::create($champs + ['client_id' => $client->id, 'champs_manquants' => []]);

        $this->appliquerNpi($personne, $client, $agent, $donnees['npi'] ?? null);

        foreach ($donnees['mandataires'] ?? [] as $ligne) {
            if (blank($ligne['nom'] ?? null)) {
                continue;
            }

            Mandataire::create([
                'personne_physique_id' => $personne->id,
                'nom' => $ligne['nom'],
                'prenoms' => $ligne['prenoms'] ?? null,
                'lien_parente' => $ligne['lien_parente'] ?? null,
            ]);
        }

        if ($agent?->estResponsableAgence() && ($donnees['fiche_rlbcft'] ?? null)) {
            $client->ficheRlbcft()->updateOrCreate([], $donnees['fiche_rlbcft']);
        }
    }

    private function creerPersonneMorale(Client $client, array $donnees, ?Agent $agent): void
    {
        $champs = array_intersect_key($donnees, array_flip([
            'raison_sociale', 'forme_juridique', 'date_creation', 'adresse', 'rccm', 'ifu',
            'telephone', 'email', 'activite_1', 'activite_2', 'revenus_mensuels_estimes',
            'beneficiaire_effectif_texte', 'droit_adhesion', 'part_sociale', 'depot_especes',
            'signature_representants_path', 'signature_responsable_nom',
            'signature_responsable_fonction', 'signature_responsable_date',
        ]));
        $personne = PersonneMorale::create($champs + ['client_id' => $client->id, 'champs_manquants' => []]);

        foreach ($donnees['signataires'] ?? [] as $ligne) {
            if (blank($ligne['nom'] ?? null)) {
                continue;
            }

            $signataire = Signataire::create([
                'personne_morale_id' => $personne->id,
                'nom' => $ligne['nom'],
                'role' => $ligne['role'] ?? 'signataire',
                'pourcentage_detention' => $ligne['pourcentage_detention'] ?? null,
                'date_naissance' => $ligne['date_naissance'] ?? null,
                'sexe' => $ligne['sexe'] ?? null,
                'lieu_naissance' => $ligne['lieu_naissance'] ?? null,
                'piece_identite_type' => $ligne['piece_identite_type'] ?? null,
                'piece_identite_numero' => $ligne['piece_identite_numero'] ?? null,
                'piece_identite_expiration' => $ligne['piece_identite_expiration'] ?? null,
                'validation_methode' => $ligne['validation_methode'] ?? null,
                'nationalite' => $ligne['nationalite'] ?? null,
                'adresse' => $ligne['adresse'] ?? null,
                'telephone' => $ligne['telephone'] ?? null,
                'fonction' => $ligne['fonction'] ?? null,
            ]);

            $this->appliquerNpi($signataire, $client, $agent, $ligne['npi'] ?? null);

            $this->moteurFiltrage->filtrer($signataire->fresh());
        }
    }

    /**
     * Mode dégradé (07_PROMPT_MODE_DEGRADE_NPI_OCR §2.3) : une absence de connexion ne
     * bloque jamais — le NPI passe en attente de rattrapage plutôt que d'être refusé.
     */
    private function appliquerNpi(PersonnePhysique|Signataire $personne, Client $client, ?Agent $agent, ?string $npi): void
    {
        if (blank($npi)) {
            return;
        }

        ['statut' => $statut, 'resultat' => $resultat] = $this->resolveurStatutNpi->resoudre($npi);

        $personne->definirNpi($npi);

        if ($statut === StatutVerificationNpi::EnAttenteConnexion) {
            $personne->saveQuietly();
            $client->update(['statut_verification_npi' => $statut]);

            VerificationNpiEnAttente::create([
                'client_id' => $client->id,
                'signataire_id' => $personne instanceof Signataire ? $personne->id : null,
                'npi_idx' => $personne->npi_idx,
                'npi_chiffre' => $npi,
                'statut' => StatutFileAttenteNpi::EnAttente,
            ]);

            Consignateur::enregistrer('agent', $agent?->id, 'npi_en_attente_connexion', 'client', $client->id);

            return;
        }

        $personne->npi_verifie_le = $resultat?->verifieLe;
        $personne->npi_verification_source = $resultat?->source;
        $personne->saveQuietly();

        if ($personne instanceof PersonnePhysique) {
            $client->update([
                'statut_verification_npi' => $statut,
                'npi_verifie_le' => $resultat?->verifieLe,
            ]);
        }
    }
}
