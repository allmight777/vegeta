<?php

namespace App\Models;

use App\Casts\Chiffre;
use App\Enums\OperateurMobileMonnaie;
use App\Enums\SourceValeur;
use Illuminate\Database\Eloquent\Model;

/**
 * Annuaire numéro → titulaire entièrement synthétique — voir la migration
 * create_comptes_mobile_monnaie_simules_table pour le contexte (remplace un vrai appel
 * USSD/API opérateur, interdit par le règlement du concours).
 */
class CompteMobileMonnaieSimule extends Model
{
    protected $table = 'comptes_mobile_monnaie_simules';

    protected $fillable = ['telephone', 'telephone_idx', 'operateur', 'nom_titulaire', 'source'];

    protected function casts(): array
    {
        return [
            'telephone' => Chiffre::class,
            'nom_titulaire' => Chiffre::class,
            'operateur' => OperateurMobileMonnaie::class,
            'source' => SourceValeur::class,
        ];
    }
}
