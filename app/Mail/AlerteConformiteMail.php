<?php

namespace App\Mail;

use App\Models\Alerte;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;

/**
 * Contenu volontairement minimal : gravité, type, agence, horodatage, lien vers le
 * tableau de bord — jamais un nom de client ni un numéro (l'e-mail est un canal moins
 * sûr que l'application, CLAUDE.md §5 "Données personnelles"). Le détail reste dans
 * l'espace responsable, jamais dans la boîte mail.
 */
class AlerteConformiteMail extends Mailable implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Alerte $alerte) {}

    public function build(): self
    {
        return $this->subject('CIF-Empreinte — nouvelle alerte de conformité ('.$this->alerte->gravite->libelle().')')
            ->view('mail.alerte-conformite');
    }
}
