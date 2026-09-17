<?php

namespace App\Models;

use App\Enums\StatutDeclarationCentif;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class DeclarationCentif extends Model
{
    use HasFactory, HasUuids, SoftDeletes;

    protected $table = 'declarations_centif';

    protected $fillable = [
        'client_id', 'montant_cumule', 'periode', 'statut', 'fichier_pdf_path', 'generee_le',
    ];

    protected function casts(): array
    {
        return [
            'montant_cumule' => 'decimal:2',
            'statut' => StatutDeclarationCentif::class,
            'generee_le' => 'datetime',
        ];
    }

    public function client(): BelongsTo
    {
        return $this->belongsTo(Client::class);
    }
}
