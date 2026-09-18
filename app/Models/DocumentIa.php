<?php

namespace App\Models;

use App\Enums\MethodeExtraction;
use App\Enums\StatutExtractionDocument;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentIa extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'documents_ia';

    protected $fillable = [
        'titre', 'nom_fichier_original', 'chemin_fichier', 'type_mime', 'taille_octets',
        'contenu_extrait', 'statut_extraction', 'methode_extraction', 'erreur_message',
        'visible_caissier', 'visible_responsable_agence', 'visible_administrateur',
        'reseau_id', 'agence_id', 'televerse_par_admin_id',
    ];

    protected function casts(): array
    {
        return [
            'taille_octets' => 'integer',
            'statut_extraction' => StatutExtractionDocument::class,
            'methode_extraction' => MethodeExtraction::class,
            'visible_caissier' => 'boolean',
            'visible_responsable_agence' => 'boolean',
            'visible_administrateur' => 'boolean',
        ];
    }

    public function reseau(): BelongsTo
    {
        return $this->belongsTo(Reseau::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function televersePar(): BelongsTo
    {
        return $this->belongsTo(Admin::class, 'televerse_par_admin_id');
    }
}
