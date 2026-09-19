<?php

use App\Enums\SourceValeur;

/*
|--------------------------------------------------------------------------
| Référentiel des fiches d'adhésion CIF
|--------------------------------------------------------------------------
|
| Source unique de vérité pour le formulaire de création/complétion client, sa
| validation et le mappeur d'extraction de documents (06_PROMPT_FORMULAIRE_CLIENT_ENRICHI
| §2). Remplace config/champs_kyc_obligatoires.php.
|
| Chaque champ porte : libelle, type_saisie (text|date|select|number|checkbox|file|npi),
| obligatoire, bloquant (empêche la validation d'une opération tant qu'il manque, cf.
| ClientPolicy::peutValiderOperation), source, reference_texte, options (select),
| saisissable (false = calculé côté serveur, affiché en lecture seule),
| contexte (« completion_uniquement » = absent du formulaire de création).
|
| Les groupes "mandataires"/"signataires" sont répétables (min/max). Le groupe
| "fiche_rlbcft" n'est jamais affiché ni accepté si l'agent n'a pas le rôle
| responsable_agence (Loi art. 63 — vérifié aussi côté serveur, pas seulement la vue).
|
*/

return [

    'personne_physique' => [

        'groupes' => [

            'identification' => [
                'libelle' => 'Identification',
                'champs' => [
                    'nom' => ['libelle' => 'Nom', 'type_saisie' => 'text', 'obligatoire' => true, 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 a)'],
                    'prenoms' => ['libelle' => 'Prénoms', 'type_saisie' => 'text', 'obligatoire' => true, 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 a)'],
                    'date_naissance' => ['libelle' => 'Date de naissance', 'type_saisie' => 'date', 'obligatoire' => true, 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 a)'],
                    'sexe' => ['libelle' => 'Sexe', 'type_saisie' => 'select', 'options' => ['m' => 'Masculin', 'f' => 'Féminin'], 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'lieu_naissance' => ['libelle' => 'Lieu de naissance', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'piece_identite_type' => ['libelle' => 'Type de pièce d\'identité', 'type_saisie' => 'select', 'options' => ['cni' => 'CNI', 'cip' => 'CIP', 'carte_biometrique' => 'Carte biométrique', 'passeport' => 'Passeport'], 'obligatoire' => true, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'piece_identite_numero' => ['libelle' => 'Numéro de pièce d\'identité', 'type_saisie' => 'text', 'obligatoire' => true, 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 b)'],
                    'npi' => ['libelle' => 'Numéro personnel d\'identification (NPI)', 'type_saisie' => 'npi', 'obligatoire' => true, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF — vérifié auprès du système officiel'],
                    'piece_identite_expiration' => ['libelle' => 'Date d\'expiration de la pièce', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'validation_methode' => ['libelle' => 'Méthode de validation', 'type_saisie' => 'select', 'options' => ['numero' => 'N° d\'identification', 'code_qr' => 'Code QR'], 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'coordonnees' => [
                'libelle' => 'Coordonnées',
                'champs' => [
                    'telephone' => ['libelle' => 'Téléphone', 'type_saisie' => 'telephone', 'obligatoire' => true, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'email' => ['libelle' => 'Email', 'type_saisie' => 'email', 'obligatoire' => true, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'adresse' => ['libelle' => 'Adresse', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 c)'],
                    'domicile' => ['libelle' => 'Domicile', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'lot' => ['libelle' => 'Lot', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'maison' => ['libelle' => 'Maison', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'quartier' => ['libelle' => 'Quartier', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'indication_maison' => ['libelle' => 'Indication spécifique — maison', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'indication_travail' => ['libelle' => 'Indication lieu de travail', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'filiation' => [
                'libelle' => 'Filiation et situation',
                'champs' => [
                    'pere' => ['libelle' => 'Père', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'mere' => ['libelle' => 'Mère', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'conjoint' => ['libelle' => 'Conjoint(e)', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'statut_matrimonial' => ['libelle' => 'Statut matrimonial', 'type_saisie' => 'select', 'options' => ['celibataire' => 'Célibataire', 'marie' => 'Marié(e)', 'divorce' => 'Divorcé(e)', 'veuf' => 'Veuf/Veuve'], 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'nationalite' => ['libelle' => 'Nationalité', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'employeur' => ['libelle' => 'Employeur', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'activite' => [
                'libelle' => 'Activité économique',
                'champs' => [
                    'profession' => ['libelle' => 'Profession', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'ifu' => ['libelle' => 'IFU (au besoin)', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'rccm' => ['libelle' => 'N° RCCM (au besoin)', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'activite_1' => ['libelle' => 'Activité 1', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'activite_2' => ['libelle' => 'Activité 2', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'revenus_mensuels_estimes' => ['libelle' => 'Revenus mensuels estimés', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'versements' => [
                'libelle' => 'Versements initiaux',
                'champs' => [
                    'droit_adhesion' => ['libelle' => 'Droit d\'adhésion', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Grille tarifaire du réseau'],
                    'part_sociale' => ['libelle' => 'Part sociale', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Grille tarifaire du réseau'],
                    'depot_especes' => ['libelle' => 'Dépôt espèces', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Grille tarifaire du réseau'],
                    'total_versements_initiaux' => ['libelle' => 'Total versements initiaux', 'type_saisie' => 'number', 'saisissable' => false, 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Calculé : droit d\'adhésion + part sociale + dépôt espèces'],
                ],
            ],

            'mandataires' => [
                'libelle' => 'Mandataires désignés',
                'repetable' => true,
                'min' => 0,
                'max' => 3,
                'champs' => [
                    'nom' => ['libelle' => 'Nom et prénoms', 'type_saisie' => 'text', 'obligatoire' => true, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'lien_parente' => ['libelle' => 'Lien de parenté', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'signatures' => [
                'libelle' => 'Signatures',
                'champs' => [
                    'signature_titulaire_path' => ['libelle' => 'Signature du titulaire ou du tuteur', 'type_saisie' => 'file', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'signature_responsable_nom' => ['libelle' => 'Nom du responsable', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'signature_responsable_fonction' => ['libelle' => 'Fonction du responsable', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'signature_responsable_date' => ['libelle' => 'Date', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'fiche_rlbcft' => [
                'libelle' => 'Fiche complémentaire RLBC/FT',
                'visible_role' => 'responsable_agence',
                'champs' => [
                    'ppe_national' => ['libelle' => 'PPE national', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §50'],
                    'ppe_etranger' => ['libelle' => 'PPE étranger', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §50'],
                    'sanction_financiere_internationale' => ['libelle' => 'Sanction financière internationale', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi LBC/FT/FP'],
                    'financement_terrorisme' => ['libelle' => 'Financement du terrorisme ou autre crime', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi LBC/FT/FP'],
                    'visa_rlbcft_nom' => ['libelle' => 'VISA RLBC/FT — nom', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 29 ; Instr. 001 art. 5'],
                    'visa_rlbcft_date' => ['libelle' => 'VISA RLBC/FT — date', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 29 ; Instr. 001 art. 5'],
                ],
            ],

        ],

    ],

    'personne_morale' => [

        'groupes' => [

            'identification' => [
                'libelle' => 'Identification de l\'entité',
                'champs' => [
                    'raison_sociale' => ['libelle' => 'Nom', 'type_saisie' => 'text', 'obligatoire' => true, 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 a)'],
                    'forme_juridique' => ['libelle' => 'Forme juridique', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 d)'],
                    'date_creation' => ['libelle' => 'Date de création', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'adresse' => ['libelle' => 'Adresse du siège social', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 c)'],
                    'rccm' => ['libelle' => 'N° autorisation, agrément ou RCCM', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 17 d)'],
                    'ifu' => ['libelle' => 'IFU (au besoin)', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'telephone' => ['libelle' => 'Téléphone', 'type_saisie' => 'telephone', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'email' => ['libelle' => 'Mail', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'signataires' => [
                'libelle' => 'Signataires',
                'repetable' => true,
                'min' => 1,
                'max' => 3,
                'champs' => [
                    'nom' => ['libelle' => 'Nom et prénoms', 'type_saisie' => 'text', 'obligatoire' => true, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'role' => ['libelle' => 'Rôle', 'type_saisie' => 'select', 'options' => ['signataire' => 'Signataire', 'beneficiaire_effectif' => 'Bénéficiaire effectif', 'mandataire' => 'Mandataire'], 'obligatoire' => true, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'pourcentage_detention' => ['libelle' => '% détention', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §12 et art. 26'],
                    'date_naissance' => ['libelle' => 'Date de naissance', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'sexe' => ['libelle' => 'Sexe', 'type_saisie' => 'select', 'options' => ['m' => 'Masculin', 'f' => 'Féminin'], 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'lieu_naissance' => ['libelle' => 'Lieu de naissance', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'piece_identite_type' => ['libelle' => 'Type de pièce', 'type_saisie' => 'select', 'options' => ['cni' => 'CNI', 'cip' => 'CIP', 'carte_biometrique' => 'Carte biométrique', 'passeport' => 'Passeport'], 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'piece_identite_numero' => ['libelle' => 'N° pièce', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'npi' => ['libelle' => 'NPI', 'type_saisie' => 'npi', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF — vérifié auprès du système officiel'],
                    'piece_identite_expiration' => ['libelle' => 'Expiration', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'validation_methode' => ['libelle' => 'Méthode de validation', 'type_saisie' => 'select', 'options' => ['numero' => 'N° d\'identification', 'code_qr' => 'Code QR'], 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'nationalite' => ['libelle' => 'Nationalité', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'adresse' => ['libelle' => 'Adresse', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'telephone' => ['libelle' => 'Téléphone', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'fonction' => ['libelle' => 'Fonction', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'signature_path' => ['libelle' => 'Signature', 'type_saisie' => 'file', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'activite' => [
                'libelle' => 'Activité économique',
                'champs' => [
                    'activite_1' => ['libelle' => 'Activité 1', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'activite_2' => ['libelle' => 'Activité 2', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'revenus_mensuels_estimes' => ['libelle' => 'Revenus mensuels estimés', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'beneficiaire_effectif_texte' => ['libelle' => 'Bénéficiaire effectif — description', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §12 et art. 26'],
                    'beneficiaire_effectif_signataire_id' => ['libelle' => 'Bénéficiaire effectif — signataire lié', 'type_saisie' => 'select', 'contexte' => 'completion_uniquement', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §12 et art. 26'],
                ],
            ],

            'versements' => [
                'libelle' => 'Versements initiaux',
                'champs' => [
                    'droit_adhesion' => ['libelle' => 'Droit d\'adhésion', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Grille tarifaire du réseau'],
                    'part_sociale' => ['libelle' => 'Part sociale', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Grille tarifaire du réseau'],
                    'depot_especes' => ['libelle' => 'Dépôt espèces', 'type_saisie' => 'number', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Grille tarifaire du réseau'],
                    'total_versements_initiaux' => ['libelle' => 'Total versements initiaux', 'type_saisie' => 'number', 'saisissable' => false, 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::PolitiqueInterne->value, 'reference_texte' => 'Calculé : droit d\'adhésion + part sociale + dépôt espèces'],
                ],
            ],

            'signatures' => [
                'libelle' => 'Signatures finales',
                'champs' => [
                    'signature_representants_path' => ['libelle' => 'Signature des représentants', 'type_saisie' => 'file', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'signature_responsable_nom' => ['libelle' => 'Nom du responsable d\'entité', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'signature_responsable_fonction' => ['libelle' => 'Fonction du responsable', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                    'signature_responsable_date' => ['libelle' => 'Date', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::BriefingCif->value, 'reference_texte' => 'Fiche d\'adhésion CIF'],
                ],
            ],

            'fiche_rlbcft' => [
                'libelle' => 'Fiche complémentaire de validation RLBC/FT (par signataire contrôlé)',
                'visible_role' => 'responsable_agence',
                'par_signataire' => true,
                'champs' => [
                    'ppe_national' => ['libelle' => 'PPE national', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §50'],
                    'ppe_etranger' => ['libelle' => 'PPE étranger', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §50'],
                    'sanction_financiere_internationale' => ['libelle' => 'Sanction financière internationale', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi LBC/FT/FP'],
                    'financement_terrorisme' => ['libelle' => 'Financement du terrorisme ou autre crime', 'type_saisie' => 'checkbox', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi LBC/FT/FP'],
                    'visa_rlbcft_nom' => ['libelle' => 'VISA RLBC/FT — nom', 'type_saisie' => 'text', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 29 ; Instr. 001 art. 5'],
                    'visa_rlbcft_date' => ['libelle' => 'VISA RLBC/FT — date', 'type_saisie' => 'date', 'obligatoire' => false, 'bloquant' => false, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 29 ; Instr. 001 art. 5'],
                ],
            ],

        ],

    ],

    // Champs de complétude calculés (jamais rendus comme un champ de saisie ordinaire),
    // gérés par des cas spéciaux dans Services\Kyc\CalculateurCompletude.
    'champs_calcules' => [
        'personne_morale' => [
            'beneficiaire_effectif' => ['libelle' => 'Bénéficiaire effectif', 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 2 §12 et art. 26'],
            'au_moins_un_signataire' => ['libelle' => 'Au moins un signataire', 'bloquant' => true, 'source' => SourceValeur::Reglementaire->value, 'reference_texte' => 'Loi art. 26'],
        ],
    ],

    // Loi art. 2 §12 et art. 26 : « plus de 25 % » du capital ou des droits de vote.
    'seuil_beneficiaire_effectif_pourcentage' => 25,

];
