<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Identité visuelle du produit (nom, logos, couleurs) : table à une seule ligne.
 * Lecture applicative via Services\Configuration\IdentiteSysteme (avec repli sur
 * les valeurs d'origine si la ligne ou la table est absente).
 */
class ConfigurationSysteme extends Model
{
    protected $table = 'configurations_systeme';

    public $timestamps = false;

    protected $fillable = [
        'nom_systeme', 'sous_titre',
        'logo_principal_path', 'logo_connexion_path', 'favicon_path',
        'couleur_primaire', 'couleur_secondaire', 'couleur_accent', 'couleur_sombre',
        'couleur_espace_caissier', 'couleur_espace_responsable', 'couleur_espace_admin',
        'couleur_page_connexion',
        'modifie_par_admin_id', 'modifie_le',
    ];

    protected function casts(): array
    {
        return [
            'modifie_le' => 'datetime',
        ];
    }
}
