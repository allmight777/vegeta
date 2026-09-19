<?php

namespace App\Mail;

use App\Models\RapportJournalier;
use App\Services\Configuration\IdentiteSysteme;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class RapportJournalierMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly RapportJournalier $rapport,
        public readonly string $codeAcces,
    ) {}

    public function build(): self
    {
        return $this->subject(IdentiteSysteme::nom().' — rapport sécurisé du '.$this->rapport->date_fin->format('d/m/Y'))
            ->view('mail.rapport-journalier');
    }
}
