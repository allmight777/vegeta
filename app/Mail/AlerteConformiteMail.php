<?php

// app/Mail/AlerteConformiteMail.php

namespace App\Mail;

use App\Services\Configuration\IdentiteSysteme;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class AlerteConformiteMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public function __construct(
        public string $gravite,
        public string $typeLibelle,
        public string $agenceNom,
        public string $explication,
        public string $lienDashboard,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: '['.IdentiteSysteme::nom().'] Alerte conformité — '.$this->typeLibelle,
        );
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.alerte-conformite',
            with: [
                'gravite' => $this->gravite,
                'typeLibelle' => $this->typeLibelle,
                'agenceNom' => $this->agenceNom,
                'explication' => $this->explication,
                'lienDashboard' => $this->lienDashboard,
            ],
        );
    }
}
