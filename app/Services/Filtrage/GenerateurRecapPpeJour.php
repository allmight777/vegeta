<?php

// app/Services/Filtrage/GenerateurRecapPpeJour.php

namespace App\Services\Filtrage;

use App\Mail\RecapPpeJourMail;
use App\Models\Agent;
use App\Models\Client;
use App\Models\ResultatFiltrage;
use App\Models\Signataire;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class GenerateurRecapPpeJour
{
    public function envoyerTousLesRecaps(): int
    {
        $jour = Carbon::today();
        $envoyes = 0;

        // Pour chaque agence ayant des résultats filtrés aujourd'hui
        $agenceIds = ResultatFiltrage::query()
            ->whereDate('created_at', $jour)
            ->where('statut', 'a_verifier')
            ->whereHas('entreeListe', function ($q) {
                $q->whereRaw('lower(categorie) like ?', ['%ppe%'])
                    ->orWhereRaw('lower(categorie) like ?', ['%sanction%']);
            })
            ->with('filtrable')
            ->get()
            ->map(function ($r) {
                $cible = $r->filtrable;

                if ($cible instanceof Client) {
                    return $cible->agence_creation_id;
                }
                if ($cible instanceof Signataire) {
                    return $cible->personneMorale?->client?->agence_creation_id;
                }

                return null;
            })
            ->filter()
            ->unique()
            ->values();

        foreach ($agenceIds as $agenceId) {
            if ($this->envoyerPourAgence($agenceId, $jour)) {
                $envoyes++;
            }
        }

        return $envoyes;
    }

    public function envoyerPourAgence(int $agenceId, Carbon $jour): bool
    {
        $clientsSuspects = $this->collecterSuspectsClients($agenceId, $jour);
        $signatairesSuspects = $this->collecterSuspectsSignataires($agenceId, $jour);
        $enAttente = $clientsSuspects->count() + $signatairesSuspects->count();

        if ($enAttente === 0) {
            return false;
        }

        $responsables = Agent::where('agence_id', $agenceId)
            ->where('role', 'responsable_agence')
            ->where('actif', true)
            ->get();

        if ($responsables->isEmpty()) {
            return false;
        }

        $agenceNom = $responsables->first()->agence?->nom ?? 'Agence '.$agenceId;
        $envoye = false;

        foreach ($responsables as $responsable) {
            if (blank($responsable->email)) {
                continue;
            }

            try {
                Mail::to($responsable->email)->queue(new RecapPpeJourMail(
                    responsableNom: $responsable->nom,
                    agenceNom: $agenceNom,
                    date: $jour->translatedFormat('d F Y'),
                    clientsSuspects: $clientsSuspects->all(),
                    signatairesSuspects: $signatairesSuspects->all(),
                    enAttente: $enAttente,
                    lienFiltrage: route('responsable.filtrage.index'),
                ));
                $envoye = true;
            } catch (\Throwable $e) {
                logger()->warning('Échec envoi récap PPE', [
                    'responsable_id' => $responsable->id,
                    'erreur' => $e->getMessage(),
                ]);
            }
        }

        return $envoye;
    }

    private function collecterSuspectsClients(int $agenceId, Carbon $jour)
    {
        return ResultatFiltrage::query()
            ->whereDate('created_at', $jour)
            ->where('statut', 'a_verifier')
            ->where('filtrable_type', Client::class)
            ->whereHas('entreeListe', function ($q) {
                $q->whereRaw('lower(categorie) like ?', ['%ppe%'])
                    ->orWhereRaw('lower(categorie) like ?', ['%sanction%']);
            })
            ->with(['entreeListe', 'filtrable'])
            ->get()
            ->filter(fn ($r) => $r->filtrable?->agence_creation_id === $agenceId)
            ->map(fn ($r) => [
                'nom' => $r->filtrable->nomAffichage(),
                'contexte' => $r->filtrable->type->value === 'personne_morale'
                    ? 'Personne morale'
                    : 'Personne physique',
                'score' => (int) round($r->score_similarite * 100),
                'source' => $r->entreeListe->source->libelle(),
                'categorie' => $r->entreeListe->categorie,
            ])
            ->values();
    }

    private function collecterSuspectsSignataires(int $agenceId, Carbon $jour)
    {
        return ResultatFiltrage::query()
            ->whereDate('created_at', $jour)
            ->where('statut', 'a_verifier')
            ->where('filtrable_type', Signataire::class)
            ->whereHas('entreeListe', function ($q) {
                $q->whereRaw('lower(categorie) like ?', ['%ppe%'])
                    ->orWhereRaw('lower(categorie) like ?', ['%sanction%']);
            })
            ->with(['entreeListe', 'filtrable.personneMorale.client'])
            ->get()
            ->filter(fn ($r) => $r->filtrable?->personneMorale?->client?->agence_creation_id === $agenceId)
            ->map(fn ($r) => [
                'nom' => $r->filtrable->nom,
                'personne_morale' => $r->filtrable->personneMorale?->raison_sociale ?? '—',
                'score' => (int) round($r->score_similarite * 100),
                'source' => $r->entreeListe->source->libelle(),
                'categorie' => $r->entreeListe->categorie,
            ])
            ->values();
    }
}
