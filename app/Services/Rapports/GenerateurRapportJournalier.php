<?php

namespace App\Services\Rapports;

use App\Enums\TypeAlerte;
use App\Models\Agence;
use App\Models\Agent;
use App\Models\Alerte;
use App\Models\Client;
use App\Models\Compte;
use App\Models\Operation;
use App\Models\RapportJournalier;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GenerateurRapportJournalier
{
    public function generer(Agence $agence, CarbonImmutable $debut, CarbonImmutable $fin, ?Agent $agent = null): RapportJournalier
    {
        $donnees = $this->donnees($agence, $debut, $fin);
        $rapport = RapportJournalier::create([
            'agence_id' => $agence->id,
            'genere_par_agent_id' => $agent?->id,
            'date_debut' => $debut->toDateString(),
            'date_fin' => $fin->toDateString(),
            'fichier_pdf_path' => '',
        ]);

        $pdf = Pdf::loadView('pdf.rapport-journalier', $donnees + [
            'agence' => $agence,
            'debut' => $debut,
            'fin' => $fin,
            'rapport' => $rapport,
        ])->setPaper('a4', 'landscape');

        $chemin = "rapports-journaliers/{$agence->id}/{$rapport->id}.pdf";
        Storage::disk('local')->put($chemin, $pdf->output());
        $rapport->update(['fichier_pdf_path' => $chemin]);

        return $rapport->fresh();
    }

    /** @return array<string, Collection|array|int> */
    public function donnees(Agence $agence, CarbonImmutable $debut, CarbonImmutable $fin): array
    {
        $operations = Operation::with(['compte.client.personnePhysique', 'compte.client.personneMorale', 'compte.client.identite'])
            ->where('agence_id', $agence->id)
            ->whereBetween('effectuee_le', [$debut->startOfDay(), $fin->endOfDay()])
            ->orderBy('effectuee_le')
            ->get();

        $seuilInhabituel = 50_000_000;
        $operationsInhabituelles = $operations->filter(fn (Operation $operation) => (float) $operation->montant >= $seuilInhabituel);
        $depots = $operations->filter(fn (Operation $operation) => $operation->type->value === 'depot');
        $depotParClient = $depots
            ->groupBy(fn (Operation $operation) => $operation->compte_id)
            ->map(fn (Collection $lignes) => $lignes->sum(fn (Operation $operation) => (float) $operation->montant));

        $comptesDormantsReactives = Alerte::with('client.personnePhysique', 'client.personneMorale')
            ->where('type', TypeAlerte::CompteDormantReactive->value)
            ->whereBetween('created_at', [$debut->startOfDay(), $fin->endOfDay()])
            ->whereHas('client.comptes', fn ($query) => $query->where('agence_id', $agence->id))
            ->get();

        // Le numéro de compte réactivé n'est pas une colonne d'Alerte (faits = jamais un
        // nom, mais un identifiant technique) : il vient de faits['compte_id'].
        $comptesParId = Compte::whereIn('id', $comptesDormantsReactives->pluck('faits.compte_id')->filter())
            ->get()->keyBy('id');

        $clients = Client::with(['personnePhysique', 'personneMorale', 'comptes'])
            ->where('agence_creation_id', $agence->id)
            ->whereBetween('created_at', [$debut->startOfDay(), $fin->endOfDay()])
            ->get();

        return [
            'operations' => $operations,
            'operationsInhabituelles' => $operationsInhabituelles,
            'depotParClient' => $depotParClient,
            'comptesDormantsReactives' => $comptesDormantsReactives,
            'comptesParId' => $comptesParId,
            'clientsPhysiques' => $clients->filter(fn (Client $client) => $client->type->value === 'personne_physique'),
            'clientsMoraux' => $clients->filter(fn (Client $client) => $client->type->value === 'personne_morale'),
            'seuilInhabituel' => $seuilInhabituel,
            'statistiques' => [
                'operations' => $operations->count(),
                'depots' => $depots->sum('montant'),
                'retraits' => $operations->filter(fn (Operation $operation) => $operation->type->value === 'retrait')->sum('montant'),
                'clients' => $clients->count(),
            ],
        ];
    }
}
