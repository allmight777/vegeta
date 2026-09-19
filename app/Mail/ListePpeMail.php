<?php

namespace App\Mail;

use App\Models\PartageListePpe;
use App\Services\Configuration\IdentiteSysteme;
use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;

class ListePpeMail extends Mailable
{
    use Queueable;

    public function __construct(
        public readonly PartageListePpe $partage,
        public readonly string $codeAcces,
    ) {}

    public function build(): self
    {
        return $this->subject(IdentiteSysteme::nom().' — liste des PPE de l\'agence')
            ->view('mail.liste-ppe');
    }
}
