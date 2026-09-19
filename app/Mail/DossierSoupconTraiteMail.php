<?php

namespace App\Mail;

use App\Services\Configuration\IdentiteSysteme;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;

/**
 * Notification NEUTRE au contrôleur permanent (18_PROMPT §7) : le dossier référencé a été traité.
 * Ni le nom du client, ni la décision, ni son sens : seulement une référence et un renvoi vers le
 * tableau de bord. Envoyée en synchrone (MAIL_MAILER=log en démonstration : rien ne sort).
 */
class DossierSoupconTraiteMail extends Mailable
{
    public function __construct(public string $reference) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: '['.IdentiteSysteme::nom().'] Dossier '.$this->reference.' traité');
    }

    public function content(): Content
    {
        return new Content(text: 'emails.dossier-soupcon-traite', with: ['reference' => $this->reference, 'lien' => route('controleur.tableau-de-bord.index')]);
    }
}
