# 01 — PROMPT SOCLE COMMUN (à exécuter en premier, par le chef d'équipe)

> **Non utilisé pour ce build** : l'équipe a retenu `05_PROMPT_MVP_RECENTRE.md` (une seule
> application resserrée) plutôt que le découpage socle/niveau 1/niveau 2 décrit ici. Conservé
> pour provenance et comme référence de conception plus complète si le temps permet d'aller
> plus loin après la définition de « terminé » du MVP resserré.

## 0. Ton rôle et ta façon de travailler

Tu es l'architecte principal Laravel du projet CIF-Empreinte. Tu construis le **socle commun** sur lequel deux binômes travailleront ensuite en parallèle (niveau 1 intra-réseau, niveau 2 inter-réseaux), puis fusionneront sans conflit.

Avant d'écrire du code :
1. Lis `CLAUDE.md` en entier. Ses règles sont obligatoires.
2. Parcours `docs/sources/` (au minimum : Loi uniforme art. 2, 16 à 30, 60 à 63, 72, 84 à 91 ; Instruction 001-03-2025 art. 5 à 8 ; briefing des règles ; note CIF-Empreinte ; document d'architecture ; photos des fiches d'adhésion ; transcription `Notes_*.pdf`).
3. Présente un plan en 15 lignes maximum, puis exécute les étapes 1 à 19 dans l'ordre sans attendre, sauf blocage réel.

Après **chaque** étape : `vendor/bin/pint`, `php artisan test`, commit `feat(socle): ...`, résumé de 5 lignes.

Ce que le socle livre : projet installé, configuration sécurisée, **schéma complet des deux niveaux**, tous les modèles, tous les enums, les contrats et événements d'échange entre niveaux, la sécurité (chiffrement, clés, audit chaîné), le moteur d'empreinte testé, l'authentification à deux espaces séparés, **toutes les routes et tous les contrôleurs en squelette**, les layouts, les écrans d'administration du socle, les seeders de référence, la documentation de base.

Ce que le socle ne livre pas : les règles de détection, le filtrage métier, les workflows de conformité (niveau 1) ; le canal CIF, les connecteurs, la synchronisation (niveau 2).

---

## Étape 1 — Initialisation

```bash
# Le dépôt contient déjà CLAUDE.md et docs/ : create-project exige un dossier vide
composer create-project laravel/laravel ../cif-tmp "12.*"
cp -rn ../cif-tmp/. . && rm -rf ../cif-tmp
php artisan install:api            # installe Sanctum + routes/api.php
composer require spatie/laravel-permission pragmarx/google2fa bacon/bacon-qr-code league/csv barryvdh/laravel-dompdf
composer require --dev larastan/larastan
npm install && npm install alpinejs chart.js html5-qrcode
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```

- Vérifie les versions réellement installées (`php artisan --version`, `composer show`) et adapte la syntaxe. Si une commande ci-dessus n'existe pas dans la version installée, trouve l'équivalent et note-le dans `docs/DECISIONS.md`.
- Tailwind via Vite (configuration fournie par Laravel 12). Aucun lien CDN nulle part.
- `phpstan.neon` : larastan, niveau 5, chemins `app/`.
- `composer.json` scripts : `test`, `lint` (pint --test), `analyse` (phpstan), `qualite` (les trois).
- Supprime la migration `create_users_table`, le modèle `App\Models\User`, sa factory et toute référence (config, seeders, tests d'exemple). Conserve les tables `sessions`, `cache`, `jobs`, `job_batches`, `failed_jobs` en recréant la migration si elle était fusionnée avec `users`.

## Étape 2 — Configuration

**`.env.example`** (valeurs vides pour les secrets, commentaires en français) :
```dotenv
APP_NAME="CIF-Empreinte"
APP_LOCALE=fr
APP_FALLBACK_LOCALE=fr
APP_TIMEZONE=UTC
CIF_MODE=central                 # central | noeud_agence | canal_cif
CIF_DEMO=true                    # active seeders et écrans de calibration ; false en production
CIF_NIVEAU2_ACTIF=true           # coupe-circuit du niveau 2
CIF_CANAL_DRIVER=local           # local | http | desactive
CIF_CANAL_URL=
CIF_REFORMULATION_DRIVER=gabarit # gabarit | llm (désactivé par défaut, jamais de donnée d'identité envoyée)
DB_CONNECTION=pgsql
SESSION_DRIVER=database
SESSION_ENCRYPT=true
SESSION_LIFETIME=30
SESSION_SECURE_COOKIE=true
SESSION_SAME_SITE=strict
QUEUE_CONNECTION=database
CACHE_STORE=database
MAIL_MAILER=log
# Clés : générées par `php artisan cles:generer`, jamais commitées, jamais en base
CLE_CIF_CHIFFREMENT_DONNEES=
CLE_CIF_EMPREINTE_INTEROP_V1=
# Une ligne par réseau et par usage, format CLE_<CODE_RESEAU>_<USAGE>_V<version>
```

**`config/cif.php`** : mode, demo, niveau2_actif, canal (driver, url, timeout), reformulation, fuseau d'affichage par défaut `Africa/Porto-Novo`, pagination, limites d'upload, rétention par défaut (lue ensuite depuis `parametres`).

**`config/cles.php`** : lecture des variables `CLE_*` et construction d'un tableau `[identifiant => base64]`. Aucune valeur par défaut.

**`config/auth.php`** :
- guards `admin` (session, provider `admins`) et `agent` (session, provider `agents`) ; guard par défaut `agent`.
- providers `admins` → `App\Models\Acces\Admin`, `agents` → `App\Models\Acces\Agent`.
- passwords `admins` (table `admin_password_reset_tokens`) et `agents` (table `agent_password_reset_tokens`), expiration 30 min.

**`config/hashing.php`** : `argon2id` si `defined('PASSWORD_ARGON2ID')`, sinon `bcrypt` avec 12 rounds. Décision notée.

**`config/logging.php`** : ajouter un processor Monolog `App\Logging\MasquageDonneesSensibles` sur tous les canaux (masque adresses e-mail, suites de 8 chiffres ou plus, et toute clé de contexte parmi `nom, prenoms, telephone, email, numero_piece, npi, adresse, date_naissance, denomination`).

**`config/permission.php`** : conserver les tables par défaut ; pas de mode équipes.

## Étape 3 — Arborescence

Crée cette arborescence (un `.gitkeep` ou un fichier réel dans chaque dossier). Les étiquettes indiquent le propriétaire : `[S]` socle, `[N1]` niveau 1, `[N2]` niveau 2.

```
app/
  Casts/                         [S] Chiffre.php, ChiffreJson.php
  Console/Commands/
    Securite/ Audit/ Empreinte/ Demo/              [S]
    Listes/ Detection/ Conformite/ Revues/          [N1]
    Canal/ Noeuds/ Integrations/                   [N2]
  Contracts/                     [S] voir étape 12
  Data/                          [S] objets de transfert immuables (readonly)
  Enums/                         [S] voir étape 4
  Events/Commun/                 [S]
  Exceptions/                    [S]
  Http/
    Controllers/Admin/<Domaine>/ voir étape 13
    Controllers/Agent/<Domaine>/
    Controllers/Api/V1/<Domaine>/
    Middleware/                  [S]
    Requests/Admin|Agent|Api/<Domaine>/  (miroir des contrôleurs, propriétaire = celui du contrôleur)
  Listeners/Intra/               [N1]
  Listeners/Interreseaux/        [N2]
  Logging/                       [S]
  Models/
    Organisation/ Acces/ Securite/ Configuration/ Clientele/ Comptes/ Empreinte/
    Listes/ Detection/ Conformite/ Integration/ Noeuds/ CanalCif/ Demo/   [S]
  Notifications/                 [N1] alertes, [N2] webhooks
  Policies/                      [S] base + une policy par modèle exposé
  Providers/                     [S] AppServiceProvider, SecuriteServiceProvider ; [N1] IntraServiceProvider ; [N2] InterreseauxServiceProvider
  Services/
    Securite/ Audit/ Parametres/ Empreinte/ Clientele/ Operations/ Stockage/ Monnaie/ Contexte/   [S]
    Kyc/ Filtrage/ Listes/ Ppe/ Risque/ Detection/ Consolidation/ Conformite/ Explication/ Rapports/   [N1]
    Interreseaux/ Integration/ Synchronisation/ Webhooks/   [N2]
  Support/                       [S] helpers purs (Bitset, Montant…)
database/
  factories/<MemeDecoupageQueModels>/
  migrations/
  seeders/  Socle/ Intra/ Interreseaux/ Demo/
docs/
  sources/ prompts/ api/ donnees/
  ARCHITECTURE.md CONFORMITE.md SECURITE.md MODE_DEGRADE.md INTEGRATION_SI_EXISTANT.md
  COMPOSANTS_TIERS.md DECISIONS.md DEMANDES_SOCLE.md GRILLE_JURY.md
lang/fr/  auth.php validation.php pagination.php passwords.php commun.php n1.php n2.php enums.php
resources/views/
  layouts/  admin.blade.php agent.blade.php invite.blade.php
  components/  (badge-source, badge-gravite, statut, tableau, champ, alerte-flash, pagination, carte-kpi, sla)
  admin/<domaine>/<ressource>/   agent/<domaine>/<ressource>/   explications/ [N1]   pdf/
routes/
  web.php (redirections uniquement)  admin/*.php  agent/*.php  api/v1/*.php  console.php
tests/
  Unit/Socle Unit/Intra Unit/Interreseaux
  Feature/Socle Feature/Intra Feature/Interreseaux Feature/Fusion
```

## Étape 4 — Enums (`app/Enums/`, tous `string`-backed, méthode `libelle()` lisant `lang/fr/enums.php`)

Table complète de ~60 enums (ModeApplication, SourceValeur, TypeInstitution, TypeAgence,
ModeConnexion, StatutActivite, PorteeAdmin, UsageCle, StatutCle, TypeClient, NatureRelation,
StatutClient, NiveauRisque, Sexe, StatutMatrimonial, CategoriePpe, FonctionPpe, LienPpe,
RolePersonne, TypeControle, ModeValidationIdentite, StatutFicheKyc, TypeCompte, StatutCompte,
TypeOperation, SensOperation, CanalOperation, TypeExecutant, StatutOperation, PorteeEmpreinte,
ModeEmpreinte, ChampEmpreinte, TypeRapprochement, DecisionRapprochement, TypeSourceListe,
FormatListe, StatutVersionListe, DeclencheurFiltrage, StatutFiltrage, DecisionCorrespondance,
GraviteAlerte, ActionRegle, ModeExecutionRegle, OrigineAlerte, StatutAlerte,
StatutDeclarationSoupcon, ModeTransmission, StatutDeclarationEspeces, StatutGel,
ObjetAutorisation, DecisionAutorisation, TypeRevue, StatutRevue, TypeConnecteur, EntiteImport,
StatutLotImport, StatutNoeud, SensSynchronisation, StatutSynchronisation, StatutParticipantCanal,
NiveauSignal, CategorieSignal, StatutSignal, JustificationRequeteCanal, ResultatCanal,
BandeScore, EvaluationRetour) — voir la version complète transmise à l'équipe pour le détail
valeur par valeur ; non reproduite ici pour ne pas alourdir ce fichier d'archive.

## Étape 5 — Migrations : schéma complet des deux niveaux (5.A à 5.J)

Organisation, sécurité/audit/paramètres, clientèle/KYC, comptes/opérations, empreintes/
rapprochements, listes de sanctions/PPE, risque/détection/alertes/conformité, intégration/
synchronisation (niveau 2), canal CIF (niveau 2), démonstration/calibration — près de 70 tables
au total avec conventions de chiffrement (`[C]`), index aveugle (`[I]`), UUID, softDeletes,
`conserver_jusqu_au`. Détail complet transmis à l'équipe séparément (document trop volumineux
pour cette archive) ; se référer à la transcription originale du prompt si la construction du
socle complet est reprise.

## Étape 6 — Modèles, Étape 7 — Sécurité, Étape 8 — Moteur d'empreinte,
## Étape 9 — Paramètres, Étape 10 — Contexte réseau, Étape 11 — Authentification/rôles,
## Étape 12 — Contrats/DTO/événements, Étape 13 — Routes et contrôleurs squelettes,
## Étape 14 — Interface commune, Étape 15 — Écrans d'administration du socle,
## Étape 16 — Seeders du socle, Étape 17 — Tests du socle, Étape 18 — Documentation,
## Étape 19 — Définition de « terminé »

Contenu détaillé identique à la version originale transmise à l'équipe (moteur d'empreinte à
1000/500 bits calibré, chiffrement AES-256-GCM par réseau, audit chaîné SHA-256, rôles
`super_admin_cif`/`admin_reseau`/`agent_guichet`/`chef_agence`/`analyste_conformite`/
`responsable_lbcft`/`haute_direction`/`controleur_interne`, contrats `MoteurFiltrage`/
`MoteurDetection`/`EvaluateurRisque`/`Reformulateur`/`ClientCanalCif`/`SourceDonnees`/
`PasserelleSms`/`VerificateurIdentiteExterne`/`TransportNoeud`, ~50 routes/contrôleurs
squelettes par espace). Non reproduit intégralement ici : l'équipe a basculé sur le MVP
resserré (`05_PROMPT_MVP_RECENTRE.md`) avant de lancer cette construction — voir
`docs/DECISIONS.md` pour la justification et les équivalents effectivement construits
(moteur d'empreinte simplifié, chiffrement à clé unique, 3 rôles agent, ~15 tables).
