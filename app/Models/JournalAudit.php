<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use RuntimeException;

/**
 * Journal d'audit chaîné (append-only) : chaque ligne inclut le hash SHA-256 de la
 * précédente. Aucune mise à jour ni suppression n'est permise (voir booted()).
 */
class JournalAudit extends Model
{
    protected $table = 'journal_audit';

    public const CREATED_AT = 'cree_le';

    public const UPDATED_AT = null;

    protected $fillable = [
        'acteur_type', 'acteur_id', 'action', 'cible_type', 'cible_id',
        'hash_precedent', 'hash_courant',
    ];

    protected function casts(): array
    {
        return [
            'cree_le' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::updating(function () {
            throw new RuntimeException('Le journal d\'audit est immuable : aucune modification permise.');
        });

        static::deleting(function () {
            throw new RuntimeException('Le journal d\'audit est immuable : aucune suppression permise.');
        });
    }
}
