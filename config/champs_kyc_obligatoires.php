<?php

use App\Enums\SourceValeur;

/*
|--------------------------------------------------------------------------
| Champs KYC obligatoires
|--------------------------------------------------------------------------
|
| Reprend les fiches d'adhésion CIF (docs/sources/) + les exigences légales qui n'y
| figurent pas toujours explicitement. "bloquant" = une opération ne peut pas être
| validée tant que ce champ manque (Policy\ClientPolicy::peutValiderOperation) ;
| les autres champs ne comptent que dans le score de complétude affiché à l'agent.
|
*/

return [

    'personne_physique' => [
        'nom' => ['libelle' => 'Nom', 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 a)'],
        'prenoms' => ['libelle' => 'Prénoms', 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 a)'],
        'date_naissance' => ['libelle' => 'Date de naissance', 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 a)'],
        'lieu_naissance' => ['libelle' => 'Lieu de naissance', 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
        'piece_identite_numero' => ['libelle' => 'Numéro de pièce d\'identité', 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 b)'],
        'piece_identite_expiration' => ['libelle' => 'Expiration de la pièce', 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
        'adresse' => ['libelle' => 'Adresse', 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 c)'],
        'profession' => ['libelle' => 'Profession', 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
        'revenus_mensuels_estimes' => ['libelle' => 'Revenus mensuels estimés', 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
    ],

    'personne_morale' => [
        'forme_juridique' => ['libelle' => 'Forme juridique', 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 d)'],
        'rccm' => ['libelle' => 'RCCM', 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 d)'],
        'ifu' => ['libelle' => 'IFU', 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
        'beneficiaire_effectif' => ['libelle' => 'Bénéficiaire effectif', 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §12 et art. 26'],
    ],

    // Loi art. 2 §12 et art. 26 : « plus de 25 % » du capital ou des droits de vote.
    'seuil_beneficiaire_effectif_pourcentage' => 25,

];
