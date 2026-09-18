<?php

namespace App\Services\Assistance;

use App\Models\Admin;
use App\Models\Agent;
use App\Models\RegleDetection;
use App\Services\Audit\Consignateur;
use App\Support\MotsInterditsConformite;

/**
 * Filtre de sortie serveur (08_PROMPT §3.1) : la seule protection qui compte vraiment
 * contre une fuite au rôle caissier, jamais une simple consigne de prompt système
 * (contournable par une question habile). S'applique après génération, quel que soit le
 * fournisseur — y compris le simulateur, au cas où sa base toucherait un jour ces sujets.
 *
 * Règle anti-fraude (10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA §5) : un caissier ne doit
 * jamais obtenir la valeur exacte d'un seuil de détection, sous aucune reformulation — un
 * caissier complice de fraude connaissant le seuil exact pourrait aider un client à
 * structurer ses dépôts pour rester juste en dessous. Ce filtre s'applique quelle que soit
 * l'origine du texte (outil, simulateur, fournisseur externe) : c'est le seul point de
 * passage garanti pour toute réponse à ce rôle.
 */
class FiltreConformiteReponseIa
{
    public const MESSAGE_NEUTRE = 'Cette question concerne un point de conformité qui doit être traité par le responsable LBC/FT. Voulez-vous que je transmette votre question ?';

    public const MESSAGE_SEUIL_MASQUE = 'Les dépôts en espèces au-delà d\'un certain montant doivent être déclarés ; le montant exact est fixé par la réglementation et n\'est pas affiché ici.';

    public function filtrer(string $reponse, Agent|Admin $utilisateur, ?int $agentId = null): string
    {
        if (! ($utilisateur instanceof Agent && $utilisateur->estCaissier())) {
            return $reponse;
        }

        if (MotsInterditsConformite::contient($reponse) !== null) {
            Consignateur::enregistrer('agent', $agentId ?? $utilisateur->id, 'assistant_ia_reponse_filtree');

            return self::MESSAGE_NEUTRE;
        }

        if ($this->contientUnSeuilExact($reponse)) {
            Consignateur::enregistrer('agent', $agentId ?? $utilisateur->id, 'assistant_ia_seuil_masque');

            return self::MESSAGE_SEUIL_MASQUE;
        }

        return $reponse;
    }

    /**
     * Cherche tout groupe de chiffres de la réponse (avec ou sans séparateur de milliers
     * — espace, espace insécable, virgule, point) qui, une fois les séparateurs retirés,
     * correspond exactement à un seuil configuré et actif.
     */
    private function contientUnSeuilExact(string $reponse): bool
    {
        $seuils = $this->seuilsActifs();

        if ($seuils === []) {
            return false;
        }

        if (preg_match_all('/\d[\d \x{00A0},.]*\d|\d/u', $reponse, $correspondances) === false) {
            return false;
        }

        foreach ($correspondances[0] as $groupe) {
            $nombre = (int) preg_replace('/\D/u', '', $groupe);

            if ($nombre > 0 && in_array($nombre, $seuils, true)) {
                return true;
            }
        }

        return false;
    }

    /**
     * @return array<int, int>
     */
    private function seuilsActifs(): array
    {
        return RegleDetection::where('actif', true)
            ->get()
            ->flatMap(fn (RegleDetection $regle) => collect($regle->parametres)
                ->filter(fn ($valeur, $cle) => is_numeric($valeur) && (str_contains($cle, 'seuil') || str_contains($cle, 'montant_min')))
                ->map(fn ($valeur) => (int) $valeur))
            ->unique()
            ->values()
            ->all();
    }
}
