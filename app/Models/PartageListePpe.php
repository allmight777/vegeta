<?php

namespace App\Models;

use App\Casts\Chiffre;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartageListePpe extends Model
{
    use HasUuids;

    protected $table = 'partages_liste_ppe';

    protected $fillable = [
        'agence_id', 'genere_par_agent_id', 'fichier_pdf_path', 'destinataire_email',
        'code_acces_hash', 'jeton_partage', 'expire_le', 'envoye_le',
    ];

    protected function casts(): array
    {
        return [
            'destinataire_email' => Chiffre::class,
            'expire_le' => 'datetime',
            'envoye_le' => 'datetime',
        ];
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }
}
