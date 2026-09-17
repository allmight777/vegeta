<?php

namespace App\Models;

use App\Enums\MethodeExtraction;
use App\Enums\StatutExtractionDocument;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DocumentClient extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'documents_clients';

    protected $fillable = [
        'import_lot_id', 'chemin_fichier', 'nom_fichier_original', 'type_mime',
        'type_client_devine', 'statut_extraction', 'methode_extraction', 'donnees_extraites',
        'client_id', 'traite_par_agent_id', 'traite_le', 'erreur_message',
    ];

    protected function casts(): array
    {
        return [
            'statut_extraction' => StatutExtractionDocument::class,
            'methode_extraction' => MethodeExtraction::class,
            'donnees_extraites' => 'array',
            'traite_le' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function traitePar(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'traite_par_agent_id');
    }

    /**
     * Nom détecté à afficher sur la mini-carte de l'écran de revue, sans jamais
     * déchiffrer une valeur qui n'a pas encore été validée par un agent.
     */
    public function nomDetecte(): string
    {
        return (string) ($this->donnees_extraites['nom']['valeur']
            ?? $this->donnees_extraites['raison_sociale']['valeur']
            ?? $this->nom_fichier_original);
    }
}
