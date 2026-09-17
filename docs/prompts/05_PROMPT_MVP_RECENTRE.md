# 05_PROMPT_MVP_RECENTRE.md — CIF-Empreinte, MVP resserré

> **Prompt effectivement suivi pour ce build.** À utiliser à la place de `01_PROMPT_SOCLE.md` +
> `02_PROMPT_NIVEAU_1_INTRA.md` si l'équipe décide de réduire le périmètre pour tenir en 3
> jours à 4 personnes. Ce prompt construit **une seule application**, pas deux niveaux séparés
> développés en parallèle. Respecte `CLAUDE.md` (stack, conventions de nommage, règles de
> conformité, sources de valeur).
>
> Ne pas construire le niveau 2 inter-réseaux réel, ni le nœud d'agence offline synchronisé.
> Ces deux sujets sont **simulés** (voir section 8) pour rester démontrables en 8 minutes sans
> risque technique. Si le temps le permet après la définition de "terminé" (section 10), voir
> `03_PROMPT_NIVEAU_2_INTER.md` pour aller plus loin — pas avant.

---

## 1. Positionnement du produit

CIF-Empreinte n'est pas un core banking. C'est une **couche de conformité LBC/FT/FP** qui vient
se greffer sur un système existant (SIG/core banking d'un SFD), quel que soit son état :
- système complet mais sans module LBC/FT ;
- système partiel (champs KYC manquants) ;
- pas de système du tout (saisie manuelle).

Elle fait quatre choses, et seulement quatre : **importer/compléter**, **filtrer**, **détecter**,
**alerter et documenter**. Toute fonctionnalité qui ne sert pas une de ces quatre choses est hors
scope pour ce MVP.

Phrase de pitch à garder en tête pendant le développement : *"On n'ajoute pas un logiciel de plus,
on ajoute le cerveau de conformité qui manque à celui qui existe déjà."*

## 2. Les 6 problèmes à résoudre — et rien d'autre

Chaque section ci-dessous correspond à un problème. Chacun doit être démontrable par **une seule
séquence d'actions à l'écran**, sans détour, avec un résultat immédiatement compréhensible par un
jury non technique.

| # | Problème | Section |
|---|---|---|
| 1 | Le core banking n'a pas tous les champs KYC → compléter, pas juste afficher | §5 |
| 2 | Une alerte de fractionnement doit s'expliquer en langage humain, pas en score | §6.1, §6.2 |
| 3 | Le fractionnement peut se faire sur plusieurs guichets/agences différentes | §6.3 |
| 4 | Une personne morale a plusieurs signataires + un bénéficiaire effectif, tous à filtrer | §5.4 |
| 5 | La direction a besoin d'un tableau de bord actionnable, pas d'un rapport de fin de journée | §7 |
| 6 | Le guichet ne doit jamais voir qu'un soupçon existe (art. 63) | §9 |

## 3. Stack — version resserrée de `CLAUDE.md`

Pour réduire les points de défaillance en démo, ce prompt simplifie deux choix de `CLAUDE.md` :

- **Une seule base : SQLite.** Pas de PostgreSQL pour ce MVP (moins de dépendances à installer sur
  les postes Windows le jour J). Toutes les migrations doivent rester compatibles PostgreSQL par
  précaution (types standards), mais la démo tourne entièrement sur SQLite.
- **Moteur d'empreinte en PHP natif, pas en service Python séparé.** La calibration (paramètres
  ci-dessous) a été faite en Python, mais l'implémentation qui tourne pendant la démo est du PHP
  pur dans `app/Services/Empreinte/`. Un appel à un processus externe est un point de rupture
  inutile pour un mode "sans connexion" — zéro dépendance de service externe au moment du pitch.
  Le script Python de calibration reste dans `tools/calibration/` comme preuve de méthode
  (argument d'Originalité/Faisabilité), mais n'est **jamais appelé en production ni en démo**.

Le reste de `CLAUDE.md` s'applique sans changement : pas de table `users`, chiffrement des champs
d'identité, contrôleurs par espace/domaine, journal d'audit chaîné, `source` obligatoire sur tout
paramètre réglementaire.

## 4. Schéma de données — minimal mais complet

Créer uniquement les tables suivantes (toutes avec `protected $table`, UUID sauf indication
contraire, `softDeletes` sur les tables de données réglementées) :

**Organisation et accès**
- `reseaux` (id auto, nom, code)
- `agences` (reseau_id, nom, code) — sert à démontrer le multi-guichets (§6.3)
- `admins` (email_idx, mot_de_passe, reseau_id nullable = admin plateforme)
- `agents` (agence_id, matricule_idx, mot_de_passe, role : `guichet` | `responsable_lbcft` | `direction`)

**Clientèle**
- `clients` (reseau_id, type : `personne_physique` | `personne_morale`, nature_relation :
  `titulaire_compte` | `occasionnel`, statut_ppe : `non_ppe` | `ppe_a_verifier` | `ppe_confirme`,
  score_completude_kyc entier 0-100, source_creation : `saisie_agent` | `import_csv`)
- `personnes_physiques` (client_id, nom chiffré, prenoms chiffré, nom_idx HMAC, date_naissance
  chiffrée, npi_idx HMAC, empreinte_nom (binary), empreinte_combinee (binary), champs_manquants
  (json — liste des clés de champs légalement requis absents))
- `personnes_morales` (client_id, raison_sociale chiffrée, raison_sociale_idx, forme_juridique,
  rccm_idx, ifu_idx, champs_manquants json)
- `signataires` (personne_morale_id, nom chiffré, nom_idx, empreinte_nom, role :
  `signataire` | `beneficiaire_effectif` | `mandataire`, pourcentage_detention nullable,
  statut_ppe, statut_filtrage)
- `import_lots` (nom_fichier, agence_id, nombre_lignes, nombre_nouveaux, nombre_a_completer,
  statut, cree_le)
- `import_lignes` (import_lot_id, client_id nullable, donnees_brutes json, champs_manquants json,
  statut : `complet` | `a_completer` | `nouveau`)

**Comptes et opérations**
- `comptes` (client_id, agence_id, numero_idx, statut : `actif` | `dormant`, derniere_operation_le)
- `operations` (compte_id, agence_id, type : `depot` | `retrait`, montant decimal(20,2),
  devise_code, effectuee_le, agent_id, canal : `guichet` | `import`)

**Filtrage et listes**
- `entrees_liste` (source : `onu` | `ppe_benin` | `ppe_cedeao` | `demo`, nom chiffré, nom_idx,
  empreinte_nom, categorie, version_liste, importee_le)
- `resultats_filtrage` (filtrable_type, filtrable_id — polymorphe client/signataire, entree_liste_id,
  score_similarite decimal, statut : `a_verifier` | `ecarte` | `confirme`, verifie_par_agent_id,
  verifie_le, motif_ecart)

**Détection et alertes**
- `regles_detection` (code, libelle, actif boolean, parametres json, source, reference_texte)
  — seeder avec 4 lignes : `FRACTIONNEMENT_GUICHET`, `FRACTIONNEMENT_MULTI_AGENCES`,
  `SEUIL_MENSUEL_CENTIF`, `COMPTE_DORMANT_REACTIVE`
- `alertes` (type, client_id, regle_detection_id nullable, resultat_filtrage_id nullable,
  gravite : `info` | `attention` | `critique`, explication_texte (généré, voir §6.2),
  faits json (**jamais de nom en clair ici**, uniquement des identifiants et des montants),
  statut : `nouvelle` | `en_cours` | `traitee`, traitee_par_agent_id, traitee_le)
- `declarations_centif` (client_id, montant_cumule, periode, statut : `a_preparer` | `generee`,
  fichier_pdf_path, generee_le)
- `journal_audit` (acteur_type, acteur_id, action, cible_type, cible_id, hash_precedent,
  hash_courant, cree_le) — chaînage SHA-256 comme dans `CLAUDE.md`

## 5. Problème 1 et 4 — import, complétude KYC, signataires

### 5.1 Champs légalement requis (référentiel, pas codé en dur)

Créer `config/champs_kyc_obligatoires.php` listant, par type de client, les clés de champ
obligatoires avec leur fondement (`source` + `reference_texte`), par exemple :
`nom`, `prenoms`, `date_naissance`, `lieu_naissance`, `piece_identite_numero`,
`piece_identite_expiration`, `adresse`, `profession`, `revenus_mensuels_estimes`,
`statut_ppe` — et pour une personne morale : `forme_juridique`, `rccm`, `ifu`, `beneficiaire_effectif`
(au moins un signataire avec `role = beneficiaire_effectif` et `pourcentage_detention` renseigné).
Ce référentiel reprend exactement les champs des fiches d'adhésion CIF (`docs/sources/`).

### 5.2 Import CSV

- `Admin\Import\ImportController` : upload d'un CSV, mapping colonnes → champs via `league/csv`,
  aperçu avant validation (ne jamais importer en aveugle).
- `Services\Import\ImportateurCsv` : pour chaque ligne, cherche un client existant par empreinte +
  date de naissance (voir §6), sinon crée un client `source_creation = import_csv`.
- Un jeu de données démo (`database/seeders/DemoCoreBankingSeeder.php`) doit générer un CSV fictif
  d'un "core banking" avec des lignes **volontairement incomplètes** (certains champs vides) pour
  que la démo du scanner de complétude ait quelque chose à montrer.

### 5.3 Scanner de complétude — le vrai livrable du problème 1

`Services\Kyc\CalculateurCompletude::evaluer(Client $client): array` :
- Compare les champs renseignés au référentiel de §5.1 selon le type de client.
- Retourne `score_completude_kyc` (0-100) et `champs_manquants` (liste des clés).
- **Règle de blocage, pas juste d'affichage** : `Policy\ClientPolicy::peutValiderOperation` refuse
  toute opération sur un client dont `champs_manquants` contient un champ marqué `bloquant = true`
  dans le référentiel (ex. bénéficiaire effectif absent sur une personne morale). Le refus doit
  afficher explicitement la liste des champs à compléter, pas un message générique.
- Écran agent : `agent/clients/{id}/completer` — formulaire pré-rempli avec ce qui existe déjà,
  champs manquants surlignés, calqué visuellement sur les fiches d'adhésion CIF (mêmes intitulés).

### 5.4 Signataires et bénéficiaire effectif (problème 4)

- Sur une fiche personne morale, `signataires` est une table à part, pas des colonnes répétées.
- Chaque signataire ajouté déclenche **automatiquement** le filtrage (§6) sur son propre nom, au
  même titre que le client principal. Aucune validation de fiche personne morale possible tant
  qu'un signataire a `statut_filtrage = a_verifier`.
- Écran admin `Admin\Ppe\SignatairesController` : vue consolidée de tous les signataires en
  attente de vérification, tous réseaux confondus.

## 6. Problème 2 et 3 — filtrage, détection, explication, multi-agences

### 6.1 Moteur d'empreinte (paramètres calibrés — ne pas les modifier sans nouvelle mesure)

`Services\Empreinte\GenerateurEmpreinte` :
- Normalisation : majuscules, sans accents (`Str::ascii`), espaces multiples réduits.
- Bigrammes sur la chaîne entourée de marqueurs `_nom_` (comme mesuré en Python).
- Hachage : HMAC-SHA256 avec clé secrète par réseau (jamais `APP_KEY`), **1000 bits / 10 fonctions
  de hachage** pour le nom complet, **500 bits / 10 fonctions** pour la date de naissance
  (format `AAAAMMJJ`).
- `Services\Empreinte\ComparateurEmpreinte::similarite(a, b): float` — coefficient de Dice.
- Score composite : `0.7 * similarite_nom + 0.3 * similarite_date_naissance` quand la date est
  disponible ; `similarite_nom` seule sinon (avec un seuil d'alerte plus haut dans ce cas, car le
  risque de faux positif est démontré plus élevé — voir mesures dans `tools/calibration/`).
- Seuils (paramètres `parametres`, `source = demo` à confirmer) : ≥ 0.85 → `a_verifier` direct ;
  0.70-0.85 → `a_verifier` avec priorité basse ; < 0.70 → non retenu.
- Blocage pour l'import de masse : **20 bandes × 3 hachages** (MinHash) pour éviter de comparer
  chaque nouveau client à toute la liste de sanctions un par un.

### 6.2 Filtrage et explication en langage humain

- `Services\Filtrage\MoteurFiltrage::filtrer(Client|Signataire $cible): Collection<ResultatFiltrage>`
  — compare l'empreinte de la cible à `entrees_liste`, crée un `ResultatFiltrage` par correspondance
  au-dessus du seuil bas.
- **Aucune alerte ne doit afficher un score brut sans phrase.** `Services\Explication\GenerateurExplication`
  produit une phrase déterministe à partir de gabarits, par exemple pour un match PPE :
  *"Le nom saisi ({initiales}) ressemble fortement à {nom_liste_masque}, inscrit sur la liste
  {source}. Vérifiez l'identité avant de poursuivre."* — jamais le nom complet de la personne
  recherchée si elle n'est pas confirmée (éviter la stigmatisation avant vérification humaine).
- Import des listes : `php artisan listes:importer --source=onu --fichier=...` lit un fichier
  local (jamais d'appel réseau pendant la démo), verse dans `entrees_liste` avec `version_liste`.
  Compléter avec une liste `ppe_benin` et `ppe_cedeao` **fictives** (`source = demo`), clairement
  annoncées comme telles dans le pitch — ne jamais prétendre avoir une vraie liste PPE officielle.

### 6.3 Détection du fractionnement — sur un guichet ET entre agences (problème 3)

C'est la fonctionnalité qui justifie le moteur d'empreinte : sans lui, ce cas est indétectable
pour un SFD classique (bases séparées par agence, pas de vue consolidée).

- `Services\Detection\DetecteurFractionnement` :
  - `FRACTIONNEMENT_GUICHET` : N opérations d'un même compte, cumul > seuil, fenêtre glissante de
    48h (paramètres dans `regles_detection.parametres`, `source = demo`).
  - `FRACTIONNEMENT_MULTI_AGENCES` : identifie le même **client physique** par
    `empreinte_combinee` (nom + date de naissance) même si le compte/numéro diffère d'une agence à
    l'autre, cumule les opérations de toutes ses agences sur la fenêtre glissante, déclenche
    l'alerte si le cumul consolidé dépasse le seuil alors qu'aucune opération individuelle ne le
    dépasse.
  - Chaque exécution est journalisée : quelles opérations ont été rapprochées et sur quel critère
    (transparence pour le responsable conformité, jamais une boîte noire).
- Explication générée pour ce cas précis : *"{N} dépôts totalisant {montant} FCFA ont été
  effectués par le même client dans {N} agences différentes en {durée}, chacun sous le seuil
  habituel. Cumulé, ce montant dépasse {seuil} FCFA."*
- `COMPTE_DORMANT_REACTIVE` : compte sans opération depuis > X mois (paramètre) avec une nouvelle
  opération → alerte `attention`, pas `critique`.
- `SEUIL_MENSUEL_CENTIF` : cumul mensuel d'un client ≥ 15 000 000 FCFA (valeur citée dans le
  briefing oral, `source = briefing_cif`, **à confirmer avec les mentors**, badge visible dans
  l'interface) → crée une ligne dans `declarations_centif` avec statut `a_preparer`.

## 7. Problème 5 — tableau de bord actionable

`Agent\TableauDeBordController` (rôle `responsable_lbcft` et `direction`) — une seule page,
quatre blocs, chaque ligne cliquable mène directement à l'action correspondante, pas à une liste
en lecture seule :

1. **Dossiers à compléter** — clients avec `score_completude_kyc` sous un seuil, triés par
   ancienneté.
2. **Alertes du jour** — `alertes` non traitées, triées par gravité, avec la phrase d'explication
   visible directement dans la liste (pas besoin de cliquer pour comprendre de quoi il s'agit).
3. **Seuils et fractionnements** — clients approchant ou dépassant le seuil CENTIF, alertes de
   fractionnement (guichet et multi-agences distingués visuellement).
4. **Déclarations CENTIF à venir** — lignes `declarations_centif` statut `a_preparer`, avec bouton
   de génération (§7.1).

### 7.1 Génération de la déclaration CENTIF (bonus, ne pas sur-investir)

- `Services\Rapports\GenerateurDeclarationCentif` produit un PDF (`barryvdh/laravel-dompdf`) à
  partir des `declarations_centif` : identité du client, opérations concernées, montant cumulé.
- **Le PDF doit afficher clairement un bandeau "Gabarit de démonstration — format officiel CENTIF
  non fourni à l'équipe"**. Ne jamais présenter ce document comme le formulaire officiel réel — le
  but est de montrer la compréhension du processus, pas de le falsifier.

## 8. Ce qui est simulé, et comment le dire à l'oral

- **Inter-réseaux (niveau 2)** : ne pas construire de vrai canal réseau. Deux `reseaux` dans la
  même base suffisent pour démontrer qu'une alerte PPE créée dans un réseau peut, via une commande
  `php artisan demo:publier-signal`, faire apparaître un signal (sans identité, juste un type de
  risque + une empreinte) consultable par l'autre réseau. Dire à l'oral : *"Nous simulons ici deux
  réseaux dans la même base pour la démo ; en production, ce canal serait un service séparé — le
  contrat d'interface (`app/Contracts/ClientCanalCif.php`) est déjà écrit pour ça."*
- **Nœud d'agence hors ligne** : ne pas construire de synchronisation SQLite réelle. Montrer que
  l'application entière tourne **sans connexion internet** (SQLite local, moteur d'empreinte en
  PHP natif, aucun appel externe) — c'est déjà la réponse à "mode dégradé". Dire à l'oral : *"L'agence
  fonctionne déjà sans connexion internet en continu ; la synchronisation entre agences est le
  prochain palier, pas un prérequis du filtrage lui-même."*
- Ne jamais improviser une réponse sur ce qui n'a pas été fait — utiliser ces deux phrases toutes
  prêtes plutôt que de bluffer.

## 9. Problème 6 — non-divulgation au guichet (art. 63)

- Le rôle `guichet` n'a **jamais** accès à `alertes`, `resultats_filtrage`, `entrees_liste`, ni au
  champ `statut_ppe` en clair. Une `Policy` dédiée bloque toute route de ces ressources pour ce
  rôle, avec test Feature qui échoue si la policy est retirée par erreur.
- Quand un client est bloqué (§5.3) ou en attente de vérification (§6.2), le guichet voit
  uniquement : *"Vérification complémentaire requise — dossier transmis au responsable
  conformité."* Jamais les mots "soupçon", "PPE", "sanction", "gel".
- Toute tentative du rôle `guichet` d'accéder à une de ces ressources (même par URL directe) est
  **journalisée** dans `journal_audit` avec action `tentative_acces_refusee`.
- Scénario de démo dédié : se connecter en `guichet`, tenter d'ouvrir la fiche d'alerte d'un client
  qu'on vient de servir → refus visible + journal affiché ensuite côté responsable conformité.

## 10. Définition de "terminé" pour ce MVP

- [ ] Les 6 problèmes ont chacun un scénario rejouable par une commande `php artisan demo:scenario X`.
- [ ] Import CSV → au moins 3 clients incomplets visibles, au moins 1 bloqué à la validation.
- [ ] Filtrage : au moins 1 match PPE fictif détecté sur un signataire, pas seulement sur un client
      principal.
- [ ] Fractionnement guichet ET fractionnement multi-agences déclenchent chacun une alerte avec
      phrase d'explication lisible.
- [ ] Tableau de bord affiche les 4 blocs avec des données réelles de la démo, chaque ligne cliquable.
- [ ] Le rôle guichet ne voit jamais un mot interdit ; test Feature correspondant présent et vert.
- [ ] `php artisan test` intégralement vert, `vendor/bin/pint` appliqué.
- [ ] README explique en 10 lignes ce qui est réel et ce qui est simulé (§8), sans ambiguïté.
- [ ] Chaque paramètre réglementaire (seuils) affiche sa `source` dans l'interface admin.

## 11. Ce qu'il ne faut surtout pas faire

- Ne pas construire les 23 règles de détection du prompt initial : **4 règles**, bien expliquées,
  suffisent largement.
- Ne pas construire le workflow complet de déclaration de soupçon (double validation, opposition
  CENTIF 4 jours, etc.) : une ligne `declarations_centif` + un PDF de démonstration suffit à
  prouver la compréhension du processus.
- Ne pas intégrer de bibliothèque tierce de filtrage/sanctions (OpenSanctions, Moov Watchman,
  etc.) : ça ajoute une dépendance externe et un temps d'apprentissage que 3 jours ne permettent
  pas d'amortir. Le moteur d'empreinte maison est déjà l'argument d'originalité — ne pas le diluer.
- Ne pas commencer le niveau 2 réel tant que la définition de "terminé" ci-dessus n'est pas cochée
  en intégralité.
