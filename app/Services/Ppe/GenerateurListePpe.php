<?php

namespace App\Services\Ppe;

use App\Models\Agence;
use App\Models\Agent;
use App\Models\Client;
use App\Models\PartageListePpe;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class GenerateurListePpe
{
    /** @return Collection<int, Client> */
    public function clientsPpe(Agence $agence, ?string $du = null, ?string $au = null): Collection
    {
        return Client::deLAgence($agence->id)
            ->when($du, fn ($q) => $q->whereDate('created_at', '>=', $du))
            ->when($au, fn ($q) => $q->whereDate('created_at', '<=', $au))
            ->where(fn ($q) => $q->where('ppe_declare', true)->orWhere('statut_ppe', '!=', 'non_ppe'))
            ->with(['personnePhysique', 'personneMorale'])
            ->withCount('documentsPpe')
            ->oldest()
            ->get();
    }

    public function generer(Agence $agence, ?Agent $agent): PartageListePpe
    {
        $partage = PartageListePpe::create([
            'agence_id' => $agence->id,
            'genere_par_agent_id' => $agent?->id,
            'fichier_pdf_path' => '',
        ]);

        $pdf = Pdf::loadView('pdf.liste-ppe', [
            'agence' => $agence,
            'clients' => $this->clientsPpe($agence),
            'genereLe' => now(),
        ])->setPaper('a4', 'landscape');

        $chemin = "listes-ppe/{$agence->id}/{$partage->id}.pdf";
        Storage::disk('local')->put($chemin, $pdf->output());
        $partage->update(['fichier_pdf_path' => $chemin]);

        return $partage->fresh();
    }
}
