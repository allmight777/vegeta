<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Enums\StatutFileAttenteNpi;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VerificationNpiEnAttente extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'verifications_npi_en_attente';

    public $timestamps = true;

    protected $fillable = [
        'client_id', 'signataire_id', 'npi_idx', 'npi_chiffre',
        'cree_le', 'derniere_tentative_le', 'tentatives', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'npi_chiffre' => Chiffre::class,
            'cree_le' => 'datetime',
            'derniere_tentative_le' => 'datetime',
            'statut' => StatutFileAttenteNpi::class,
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function signataire(): BelongsTo
    {
        return $this->belongsTo(Signataire::class);
    }
}
