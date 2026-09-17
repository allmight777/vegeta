<?php

namespace App\Services\Assistance;

use App\Models\EscaladeAssistantIa;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

/**
 * Charge la base de connaissances utilisée par le simulateur (08_PROMPT §6) : les
 * fichiers statiques de resources/assistance/*.md, fusionnés avec les escalades déjà
 * traitées par un responsable (question + reponse_responsable, source =
 * reponse_responsable) — boucle d'amélioration continue sans jamais réécrire un fichier
 * versionné au vol (voir docs/DECISIONS.md).
 */
class BaseConnaissances
{
    /**
     * @return Collection<int, array{question: string, reponse: string, source: string}>
     */
    public function charger(): Collection
    {
        return $this->chargerFichiers()->merge($this->chargerEscaladesTraitees());
    }

    /**
     * @return array{question: string, reponse: string, source: string}|null
     */
    public function rechercher(string $question): ?array
    {
        $motsQuestion = $this->normaliser($question);

        if ($motsQuestion === []) {
            return null;
        }

        $meilleure = null;
        $meilleurScore = 0;

        foreach ($this->charger() as $entree) {
            $motsEntree = $this->normaliser($entree['question']);
            $score = count(array_intersect($motsQuestion, $motsEntree));

            if ($score > $meilleurScore) {
                $meilleurScore = $score;
                $meilleure = $entree;
            }
        }

        return $meilleurScore > 0 ? $meilleure : null;
    }

    /**
     * @return Collection<int, array{question: string, reponse: string, source: string}>
     */
    private function chargerFichiers(): Collection
    {
        $entrees = collect();

        foreach (File::glob(resource_path('assistance/*.md')) as $chemin) {
            $entrees = $entrees->merge($this->parser(File::get($chemin)));
        }

        return $entrees;
    }

    /**
     * @return array<int, array{question: string, reponse: string, source: string}>
     */
    private function parser(string $contenu): array
    {
        $entrees = [];

        if (preg_match_all('/^##\s*\[(\w+)\]\s*(.+?)\s*$(.*?)(?=^##\s*\[|\z)/msu', $contenu, $correspondances, PREG_SET_ORDER) === false) {
            return $entrees;
        }

        foreach ($correspondances as $correspondance) {
            $reponse = trim($correspondance[3]);

            if ($reponse === '') {
                continue;
            }

            $entrees[] = [
                'question' => trim($correspondance[2]),
                'reponse' => $reponse,
                'source' => $correspondance[1],
            ];
        }

        return $entrees;
    }

    /**
     * @return Collection<int, array{question: string, reponse: string, source: string}>
     */
    private function chargerEscaladesTraitees(): Collection
    {
        return EscaladeAssistantIa::where('statut', 'traitee')
            ->whereNotNull('reponse_responsable')
            ->get()
            ->map(fn (EscaladeAssistantIa $escalade) => [
                'question' => $escalade->question,
                'reponse' => $escalade->reponse_responsable,
                'source' => 'reponse_responsable',
            ]);
    }

    /**
     * @return array<int, string>
     */
    private function normaliser(string $texte): array
    {
        $ascii = Str::of($texte)->ascii()->lower()->toString();
        $nettoye = preg_replace('/[^a-z0-9 ]/', ' ', $ascii) ?? '';

        return array_values(array_filter(explode(' ', $nettoye), fn (string $mot) => mb_strlen($mot) > 2));
    }
}
