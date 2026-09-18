<?php

namespace App\Models;

use App\Casts\Chiffre;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RapportJournalier extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rapports_journaliers';

    protected $fillable = [
        'agence_id', 'genere_par_agent_id', 'date_debut', 'date_fin', 'fichier_pdf_path',
        'destinataire_email', 'code_acces_hash', 'jeton_partage', 'expire_le', 'envoye_le',
    ];

    protected function casts(): array
    {
        return [
            'date_debut' => 'date',
            'date_fin' => 'date',
            'destinataire_email' => Chiffre::class,
            'expire_le' => 'datetime',
            'envoye_le' => 'datetime',
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function generePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'genere_par_agent_id');
    }
}
