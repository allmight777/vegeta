<?php
// app/Mail/AlertePpeSuspecteMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlertePpeSuspecteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{nom_liste:string,source:string,categorie:string,score:int}>  $correspondances
     */
    public function __construct(
        public string $responsableNom,
        public string $clientNom,
        public string $contexte,
        public array $correspondances,
        public string $lienFiltrage,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[CIF-Empreinte] Alerte PPE — nouveau client ou signataire à vérifier',
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerte-ppe-suspecte',
            with: [
                'responsableNom' => $this->responsableNom,
                'clientNom' => $this->clientNom,
                'contexte' => $this->contexte,
                'correspondances' => $this->correspondances,
                'lienFiltrage' => $this->lienFiltrage,
            ],
        );
    }
}
