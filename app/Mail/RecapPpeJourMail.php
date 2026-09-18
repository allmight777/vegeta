<?php
// app/Mail/RecapPpeJourMail.php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class RecapPpeJourMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * @param  array<int, array{nom:string,contexte:string,score:int,source:string,categorie:string}>  $clientsSuspects
     * @param  array<int, array{nom:string,personne_morale:string,score:int,source:string,categorie:string}>  $signatairesSuspects
     */
    public function __construct(
        public string $responsableNom,
        public string $agenceNom,
        public string $date,
        public array $clientsSuspects,
        public array $signatairesSuspects,
        public int $enAttente,
        public string $lienFiltrage,
    ) {
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '[CIF-Empreinte] Récapitulatif quotidien PPE — '.$this->date,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.recap-ppe-jour',
            with: [
                'responsableNom' => $this->responsableNom,
                'agenceNom' => $this->agenceNom,
                'date' => $this->date,
                'clientsSuspects' => $this->clientsSuspects,
                'signatairesSuspects' => $this->signatairesSuspects,
                'enAttente' => $this->enAttente,
                'lienFiltrage' => $this->lienFiltrage,
            ],
        );
    }
}
