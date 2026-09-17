<?php

namespace App\Services\Assistance;

use App\Models\Admin;
use App\Models\Agent;
use App\Services\Audit\Consignateur;
use App\Support\MotsInterditsConformite;

/**
 * Filtre de sortie serveur (08_PROMPT §3.1) : la seule protection qui compte vraiment
 * contre une fuite au rôle guichet, jamais une simple consigne de prompt système
 * (contournable par une question habile). S'applique après génération, quel que soit le
 * fournisseur — y compris le simulateur, au cas où sa base toucherait un jour ces sujets.
 */
class FiltreConformiteReponseIa
{
    public const MESSAGE_NEUTRE = 'Cette question concerne un point de conformité qui doit être traité par le responsable LBC/FT. Voulez-vous que je transmette votre question ?';

    public function filtrer(string $reponse, Agent|Admin $utilisateur, ?int $agentId = null): string
    {
        if (! ($utilisateur instanceof Agent && $utilisateur->estGuichet())) {
            return $reponse;
        }

        if (MotsInterditsConformite::contient($reponse) === null) {
            return $reponse;
        }

        Consignateur::enregistrer('agent', $agentId ?? $utilisateur->id, 'assistant_ia_reponse_filtree');

        return self::MESSAGE_NEUTRE;
    }
}
