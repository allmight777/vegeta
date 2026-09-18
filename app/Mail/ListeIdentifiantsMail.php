<?php

namespace App\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Attachment;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class ListeIdentifiantsMail extends Mailable
{
    use Queueable, SerializesModels;

    public function __construct(
        public readonly string $cheminPdf,
        public readonly string $motDePassePdf,
    ) {}

    public function envelope(): Envelope
    {
        return new Envelope(subject: 'Liste des identifiants agents');
    }

    public function content(): Content
    {
        return new Content(
            view: 'emails.agents.liste-identifiants',
            with: ['motDePasse' => $this->motDePassePdf],
        );
    }

    public function attachments(): array
    {
        return [
            Attachment::fromPath($this->cheminPdf)
                ->as('identifiants-agents.pdf')
                ->withMime('application/pdf'),
        ];
    }
}
