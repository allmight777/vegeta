<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/** Pièce justificative de la qualité de PPE (Loi art. 29) — conservée, jamais supprimée physiquement. */
class DocumentPpe extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'documents_ppe';

    protected $fillable = ['client_id', 'chemin_fichier', 'nom_fichier_original', 'type_mime'];

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
