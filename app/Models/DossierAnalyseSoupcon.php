<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Enums\AvisTechniqueSoupcon;
use App\Enums\CanalSoupcon;
use App\Enums\NiveauRisqueSoupcon;
use App\Enums\StatutDossierSoupcon;
use App\Enums\TypeClientSoupcon;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * Fiche d'analyse de soupçon LBC/FT (structure du gabarit FECECAM-BENIN, 18_PROMPT §3) en
 * colonnes structurées. Le nom, le numéro de compte et la date d'ouverture de la section 1 sont
 * lus sur le client au moment de l'affichage, jamais dupliqués ici. `resume_faits` et
 * `analyse_controleur` (texte libre, potentiellement identifiant) sont chiffrés.
 */
class DossierAnalyseSoupcon extends Model
{
    use HasUuids, SoftDeletes;

    protected $table = 'dossiers_analyse_soupcon';

    protected $fillable = [
        'suggestion_soupcon_id', 'client_id', 'controleur_id', 'responsable_id',
        'type_client', 'niveau_risque', 'dates_operations', 'montants_concernes', 'canal', 'resume_faits',
        'indicateurs', 'indicateur_autre_texte',
        'analyse_controleur', 'avis_technique_controleur', 'transmis_le',
        'avis_technique_responsable', 'decide_le', 'statut',
    ];

    protected function casts(): array
    {
        return [
            'type_client' => TypeClientSoupcon::class,
            'niveau_risque' => NiveauRisqueSoupcon::class,
            'canal' => CanalSoupcon::class,
            'dates_operations' => 'array',
            'montants_concernes' => 'array',
            'resume_faits' => Chiffre::class,
            'indicateurs' => 'array',
            'analyse_controleur' => Chiffre::class,
            'avis_technique_controleur' => AvisTechniqueSoupcon::class,
            'avis_technique_responsable' => AvisTechniqueSoupcon::class,
            'transmis_le' => 'datetime',
            'decide_le' => 'datetime',
            'statut' => StatutDossierSoupcon::class,
        ];
    }

    public function suggestion(): BelongsTo
    {
        return $this->belongsTo(SuggestionSoupcon::class, 'suggestion_soupcon_id');
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    public function controleur(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'controleur_id');
    }

    public function responsable(): BelongsTo
    {
        return $this->belongsTo(Agent::class, 'responsable_id');
    }

    /** Référence neutre, sans identité : utilisée dans les e-mails et les journaux. */
    public function reference(): string
    {
        return 'DSS-'.strtoupper(substr($this->id, 0, 8));
    }
}
