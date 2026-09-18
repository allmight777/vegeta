<?php

namespace App\Models;

use App\Enums\MethodeRattachement;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RattachementIdentite extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'rattachements_identite';

    protected $fillable = ['identite_id', 'client_id', 'methode', 'score', 'decide_par_agent_id', 'motif'];

    protected function casts(): array
    {
        return [
            'methode' => MethodeRattachement::class,
            'score' => 'decimal:4',
        ];
    }

    public function identite(): BelongsTo
    {
        return $this->belongsTo(Identite::class);
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }

    /** Phrase lisible pour l'écran « pourquoi ces comptes sont-ils liés ? ». */
    public function justification(): string
    {
        return $this->methode === MethodeRattachement::Empreinte
            ? $this->methode->libelle().' — similarité '.round(((float) $this->score) * 100).' %'
            : $this->methode->libelle();
    }
}
