<?php

namespace App\Models;

use App\Enums\CanalOperation;
use App\Enums\ModePaiement;
use App\Enums\TypeOperation;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use RuntimeException;

class Operation extends Model
{
    use HasFactory, HasUuids;

    protected $table = 'operations';

    protected $fillable = [
        'compte_id', 'agence_id', 'agent_id', 'type', 'montant', 'mode_paiement',
        'devise_code', 'effectuee_le', 'canal',
    ];

    protected function casts(): array
    {
        return [
            'type' => TypeOperation::class,
            'canal' => CanalOperation::class,
            'mode_paiement' => ModePaiement::class,
            'montant' => 'decimal:2',
            'effectuee_le' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::deleting(function () {
            throw new RuntimeException('Une opération ne peut jamais être supprimée (Loi uniforme, conservation).');
        });
    }

    public function compte(): BelongsTo
    {
        return $this->belongsTo(Compte::class);
    }

    public function agence(): BelongsTo
    {
        return $this->belongsTo(Agence::class);
    }

    public function agent(): BelongsTo
    {
        return $this->belongsTo(Agent::class);
    }
}
