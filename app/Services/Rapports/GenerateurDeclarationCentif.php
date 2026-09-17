<?php

namespace App\Services\Rapports;

use App\Models\DeclarationCentif;
use App\Models\Operation;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Storage;

/**
 * PDF de démonstration — jamais présenté comme le formulaire officiel CENTIF réel
 * (arrêté fixant le modèle non fourni à l'équipe, voir CLAUDE.md §3 et §7.1).
 */
class GenerateurDeclarationCentif
{
    public function generer(DeclarationCentif $declaration): string
    {
        $client = $declaration->client->loadMissing(['personnePhysique', 'personneMorale']);

        [$debut, $fin] = $this->bornesPeriode($declaration->periode);
        $operations = Operation::whereHas('compte', fn ($q) => $q->where('client_id', $client->id))
            ->whereBetween('effectuee_le', [$debut, $fin])
            ->orderBy('effectuee_le')
            ->get();

        $pdf = Pdf::loadView('pdf.declaration-centif', [
            'declaration' => $declaration,
            'client' => $client,
            'operations' => $operations,
        ]);

        $chemin = 'declarations-centif/'.$declaration->id.'.pdf';
        Storage::disk('local')->put($chemin, $pdf->output());

        $declaration->update([
            'statut' => 'generee',
            'fichier_pdf_path' => $chemin,
            'generee_le' => now(),
        ]);

        return $chemin;
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function bornesPeriode(string $periode): array
    {
        $debut = Carbon::createFromFormat('Y-m-d', $periode.'-01')->startOfDay();

        return [$debut, $debut->copy()->endOfMonth()];
    }
}
