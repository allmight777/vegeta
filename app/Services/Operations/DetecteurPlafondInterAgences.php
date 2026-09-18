<?php

// app/Services/Operations/DetecteurPlafondInterAgences.php

namespace App\Services\Operations;

use App\Enums\GraviteAlerte;
use App\Enums\TypeAlerte;
use App\Mail\AlerteConformiteMail;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Operation;
use App\Models\RegleDetection;
use App\Services\Audit\Consignateur;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

/**
 * Après chaque dépôt en espèces, vérifie le cumul journalier de la personne
 * (via son identité, toutes agences du réseau confondues) et déclenche une
 * alerte si le seuil de fractionnement multi-agences est dépassé.
 *
 * Le caissier n'est JAMAIS notifié — conformément au principe « l'outil
 * recommande, l'humain décide » : l'opération est enregistrée normalement,
 * seule la chaîne conformité est avertie.
 */
class DetecteurPlafondInterAgences
{
    public function analyserCumulJournalier(Operation $operation): void
    {
        $client = $operation->compte?->client;

        if ($client === null) {
            return;
        }

        $identite = $client->identite;

        if ($identite === null) {
            return;
        }

        // Récupère la règle active pour le seuil (source = demo ou briefing)
        $regle = RegleDetection::where('code', 'FRACTIONNEMENT_MULTI_AGENCES')
            ->where('actif', true)
            ->first();

        if ($regle === null) {
            return;
        }

        $seuil = (float) ($regle->parametres['seuil_cumul']
    ?? $regle->parametres['seuil_journalier']
    ?? $regle->parametres['seuil']
    ?? 0);

        if ($seuil <= 0) {
            return;
        }

        $debutJournee = Carbon::today();
        $finJournee = Carbon::tomorrow();

        $clientIds = $identite->clientIds();

        // Cumul de tous les dépôts en espèces du jour, sur tous les comptes
        // de la personne, toutes agences du réseau confondues
        $operations = Operation::query()
            ->whereHas('compte', fn ($q) => $q->whereIn('client_id', $clientIds))
            ->where('type', 'depot')
            ->where('mode_paiement', 'especes')
            ->whereBetween('effectuee_le', [$debutJournee, $finJournee])
            ->with(['compte.agence'])
            ->get();

        $cumul = (float) $operations->sum('montant');

        if ($cumul < $seuil) {
            return;
        }

        // Anti-doublon : une seule alerte par identité et par jour
        $dejaAlerte = Alerte::where('type', TypeAlerte::FractionnementMultiAgences)
            ->where('faits->identite_id', $identite->id)
            ->whereDate('created_at', today())
            ->exists();

        if ($dejaAlerte) {
            return;
        }

        $agences = $operations
            ->pluck('compte.agence.nom')
            ->filter()
            ->unique()
            ->values();

        $agenceSaisie = $operation->compte?->agence;

        $faits = [
            'identite_id' => $identite->id,
            'cumul_journalier' => $cumul,
            'seuil' => $seuil,
            'nb_operations' => $operations->count(),
            'nb_agences' => $agences->count(),
            'agences' => $agences->all(),
            'agence_saisie' => $agenceSaisie?->nom,
        ];

        $explication = sprintf(
            'Cumul journalier de %s XOF en espèces (%d opération%s dans %d agence%s) — seuil %s XOF dépassé.',
            number_format($cumul, 0, ',', ' '),
            $operations->count(),
            $operations->count() > 1 ? 's' : '',
            $agences->count(),
            $agences->count() > 1 ? 's' : '',
            number_format($seuil, 0, ',', ' '),
        );

        // Alerte visible dans l'espace responsable de l'agence de saisie
        $alerte = Alerte::create([
            'type' => TypeAlerte::FractionnementMultiAgences,
            'gravite' => $cumul >= $seuil * 1.5
                ? GraviteAlerte::Critique
                : GraviteAlerte::Attention,
            'agence_id' => $agenceSaisie?->id,
            'client_id' => $operation->compte?->client_id,
            'explication_texte' => $explication,
            'faits' => $faits,
        ]);

        // Notification e-mail aux responsables de l'agence de saisie UNIQUEMENT.
        // Jamais au caissier connecté (rôle exclu), jamais à l'admin (pas dans cette table).
        $responsables = Agent::where('agence_id', $agenceSaisie?->id)
            ->where('role', 'responsable_agence')
            ->where('actif', true)
            ->get();

        foreach ($responsables as $responsable) {
            if (blank($responsable->email)) {
                continue;
            }

            Mail::to($responsable->email)->queue(new AlerteConformiteMail(
                gravite: $alerte->gravite->value,
                typeLibelle: $alerte->type->libelle(),
                agenceNom: $agenceSaisie?->nom ?? '—',
                explication: $explication,
                lienDashboard: route('responsable.tableau-de-bord.index'),
            ));
        }

        Consignateur::enregistrer(
            'systeme',
            null,
            'alerte_fractionnement_multi_agences',
            'alerte',
            $alerte->id,
            ['identite_id' => $identite->id, 'cumul' => $cumul, 'seuil' => $seuil]
        );
    }
}
