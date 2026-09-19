<?php

namespace App\Services\Kyc;

use App\Enums\GraviteAlerte;
use App\Enums\RoleAgent;
use App\Enums\StatutAlerte;
use App\Enums\TypeAlerte;
use App\Mail\AlerteConformiteMail;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\PersonnePhysique;
use App\Services\Audit\Consignateur;
use App\Services\Empreinte\ComparateurEmpreinte;
use App\Services\Empreinte\GenerateurEmpreinte;
use App\Services\Explication\GenerateurExplication;
use Illuminate\Support\Facades\Mail;

/**
 * Contrôle de conformité, pas un blocage : « l'outil recommande, l'humain décide »
 * (CLAUDE.md §3). Le caissier n'est jamais empêché de poursuivre — seul le responsable de
 * l'agence est alerté (tableau de bord + e-mail), jamais le caissier ni l'administrateur.
 * Même seuil de similarité que VerificateurTelephoneExistant (0.7), voir docs/DECISIONS.md.
 */
class DetecteurIncoherenceDepotSimule
{
    private const SEUIL_RESSEMBLANCE_NOM = 0.7;

    public function __construct(
        private readonly SimulateurDepotMobileMonnaie $simulateur,
        private readonly GenerateurEmpreinte $generateur,
        private readonly ComparateurEmpreinte $comparateur,
        private readonly GenerateurExplication $explication,
    ) {}

    public function verifier(Client $client, ?Agent $agent): void
    {
        [$telephone, $nomSaisi] = $this->extraireTelephoneEtNom($client);

        if (blank($telephone) || blank($nomSaisi)) {
            return;
        }

        $resultat = $this->simulateur->simuler($telephone);

        if (! $resultat->trouve || blank($resultat->nomTitulaire)) {
            return;
        }

        $score = $this->comparateur->dice(
            $this->generateur->encoderNom($nomSaisi),
            $this->generateur->encoderNom($resultat->nomTitulaire),
        );

        if ($score >= self::SEUIL_RESSEMBLANCE_NOM) {
            return;
        }

        if ($this->alerteDejaOuverte($client)) {
            return;
        }

        $alerte = Alerte::create([
            'type' => TypeAlerte::IncoherenceDepotSimule,
            'client_id' => $client->id,
            'gravite' => GraviteAlerte::Attention,
            'explication_texte' => $this->explication->pourIncoherenceDepotSimule(
                $resultat->operateur?->libelle() ?? 'inconnu',
                $score,
            ),
            'faits' => [
                'operateur' => $resultat->operateur?->value,
                'score_similarite' => round($score, 4),
            ],
            'statut' => StatutAlerte::Nouvelle,
        ]);

        $this->notifierResponsablesAgence($client, $alerte);

        Consignateur::enregistrer('agent', $agent?->id, 'detection_incoherence_depot_simule', 'client', $client->id);
    }

    /**
     * @return array{0: ?string, 1: ?string}
     */
    private function extraireTelephoneEtNom(Client $client): array
    {
        $personne = $client->personnePhysique ?? $client->personneMorale;

        if ($personne === null) {
            return [null, null];
        }

        $nom = $personne instanceof PersonnePhysique
            ? trim($personne->prenoms.' '.$personne->nom)
            : $personne->raison_sociale;

        return [$personne->telephone, $nom];
    }

    private function alerteDejaOuverte(Client $client): bool
    {
        return Alerte::where('client_id', $client->id)
            ->where('type', TypeAlerte::IncoherenceDepotSimule)
            ->where('statut', '!=', StatutAlerte::Traitee)
            ->exists();
    }

    /**
     * Uniquement les responsables de l'agence de création du client, jamais un autre
     * réseau ni une autre agence (même scope que le tableau de bord), jamais un caissier
     * (rôle exclu de la requête) ni un administrateur (autre table, jamais interrogée).
     */
    private function notifierResponsablesAgence(Client $client, Alerte $alerte): void
    {
        if ($client->agence_creation_id === null) {
            return;
        }

        $agence = Agence::find($client->agence_creation_id);

        $responsables = Agent::where('agence_id', $client->agence_creation_id)
            ->where('role', RoleAgent::ResponsableAgence)
            ->whereNotNull('email_idx')
            ->get();

        foreach ($responsables as $responsable) {
            if (filled($responsable->email)) {
                // Un SMTP indisponible ne doit jamais bloquer la création du client.
                try {
                    Mail::to($responsable->email)->queue(new AlerteConformiteMail(
                        gravite: $alerte->gravite->value,
                        typeLibelle: $alerte->type->libelle(),
                        agenceNom: $agence?->nom ?? '—',
                        explication: (string) $alerte->explication_texte,
                        lienDashboard: route('responsable.tableau-de-bord.index'),
                    ));
                } catch (\Throwable $e) {
                    logger()->warning('Échec envoi alerte incohérence dépôt', [
                        'alerte_id' => $alerte->id,
                        'responsable_id' => $responsable->id,
                        'erreur' => $e->getMessage(),
                    ]);
                }
            }
        }
    }
}
