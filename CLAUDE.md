# CLAUDE.md — CIF-Empreinte (équipe VEGETA)

> Ce fichier est placé à la racine du dépôt. Claude Code le relit à chaque session.
> Il s'applique aux 4 membres et aux 3 phases (socle, niveau 1, niveau 2, fusion).
> En cas de conflit entre ce fichier et un prompt, ce fichier l'emporte, sauf décision écrite dans `docs/DECISIONS.md`.
>
> **Note de build (2026-09-17)** : ce dépôt a finalement été construit avec
> `docs/prompts/05_PROMPT_MVP_RECENTRE.md` (MVP resserré, une seule application) plutôt
> qu'avec le découpage socle/niveau 1/niveau 2 décrit plus bas et dans `01_PROMPT_SOCLE.md`/
> `02_PROMPT_NIVEAU_1_INTRA.md`. Les règles de conformité, la stack et les conventions de
> code de ce fichier restent la référence ; voir `docs/DECISIONS.md` pour les simplifications
> assumées par le MVP resserré (SQLite seul, un seul niveau d'accès aux données, etc.).

---

## 1. Contexte en 10 lignes

- Événement : Hackathon National d'Innovation CIF — projet DigiCoop-WA+ — Bénin, 18 au 20 septembre 2026. Gel du code : jour 3 à 14h00.
- Thématique 01 : filtrage automatisé des clients et des transactions au regard des listes de sanctions LBC/FT/FP et des profils PPE, adapté aux SFD d'Afrique de l'Ouest.
- Solution : **CIF-Empreinte**. Chaque identité est transformée en empreinte (normalisation → bigrammes → HMAC-SHA256 avec clé secrète → filtre de Bloom). Deux empreintes de variantes d'un même nom restent proches ; aucune ne permet de relire le nom.
- **Niveau 1 — intra-réseau (MVP obligatoire)** : KYC complet, filtrage sanctions/PPE, cotation, consolidation d'un même client multi-agences, détection du fractionnement et des opérations inhabituelles, alertes temps réel, dossiers de conformité (déclaration de soupçon, déclaration des transactions en espèces, gel).
- **Niveau 2 — inter-réseaux et interopérabilité externe** : canal CIF de signaux de risque sans identité, connecteurs vers tout système existant (core banking, CSV/Excel, API, saisie manuelle), nœud d'agence hors ligne synchronisé.
- Textes de référence dans `docs/sources/` : Loi uniforme LBC/FT/FP (UMOA, 2023), Instruction BCEAO n°001-03-2025, briefing du jury, charte PI, note de présentation, fiches d'adhésion CIF (photos), transcription du briefing oral CIF.

## 2. Règles du concours — non négociables

1. **Aucune donnée personnelle réelle.** Pas de fichier client, pas d'extraction d'un système existant. Toutes les données de démonstration sont synthétiques et générées par commande. Seule exception : les listes officielles publiques (liste consolidée ONU), importées depuis un fichier local.
2. **Terminal standard** : fonctionne sous Windows (PHP + SQLite, ex. Laragon ou Herd) et dans le navigateur d'un Android d'entrée de gamme. Pages légères, pas de CDN, tous les assets compilés localement.
3. **Mode dégradé** : la caisse doit pouvoir filtrer et saisir hors connexion (nœud d'agence), puis synchroniser.
4. **Code versionné et documenté** : README lisible par un tiers, docs dans `docs/`.
5. **Démonstration live** obligatoire : scénarios rejouables par commande, sans dépendance au wifi.
6. **Briques tierces et outils d'IA déclarés** dans `docs/COMPOSANTS_TIERS.md` et dans le README (Claude Code inclus).
7. **Charte PI** : le code est cédé à la CIF. N'utiliser que des dépendances sous licence permissive (MIT, BSD, Apache-2.0). Toute dépendance GPL/AGPL est interdite sans décision écrite.

## 3. Règles de conformité réglementaire — à respecter dans chaque ligne de code

| Règle | Fondement | Traduction technique |
|---|---|---|
| Aucune valeur réglementaire codée en dur | La Loi uniforme renvoie les seuils à « l'autorité compétente » (art. 17, 21, 72) | Tout seuil est une ligne de `parametres` ou `tranches_cotation` avec `source` et `reference_texte` |
| Ne jamais inventer une valeur ni un format officiel | Modèle de DOS fixé par arrêté (art. 60), non fourni | Gabarits d'export configurables, marqués « format officiel non fourni » |
| « Sans délai » = 24 heures maximum | Loi art. 2 §58 ; Instr. 001-03-2025 art. 2 §23 et art. 6 | Mesure et affichage du délai entre publication d'une liste et fin du refiltrage ; échéance SLA sur les gels |
| Gel immédiat, sans informer le titulaire | Loi art. 89 à 91 | Blocage conservatoire automatique sur correspondance forte, confirmation humaine tracée |
| Interdiction de divulguer une déclaration de soupçon | Loi art. 63 | Le rôle guichet ne voit jamais « soupçon », « DOS », « sanction » ni le nom listé ; message neutre « vérification complémentaire requise » |
| Conservation 10 ans | Loi art. 23 | Aucune suppression physique des données réglementées ; `softDeletes` + `conserver_jusqu_au` |
| PPE : autorisation de la haute direction, origine des fonds et du patrimoine, surveillance renforcée, réévaluation tous les 3 ans | Loi art. 29 ; Instr. 001 art. 5 | Workflow `autorisations_direction`, champs origine fonds/patrimoine, échéances `revues_periodiques` |
| Profil client mis à jour sous 1 mois | Instr. 001 art. 6 | Échéance générée à chaque information modifiant le profil |
| Bénéficiaire effectif : plus de 25 % du capital ou des droits de vote, ou contrôle par autre moyen, sinon dirigeant principal | Loi art. 2 §12 et art. 26 | Contrôle de cohérence à la validation d'une fiche personne morale |
| Opérations en espèces multiples d'une même personne dans la journée = opération unique | Loi art. 17 i) | Règle de cumul journalier espèces |
| Personne agissant pour le compte du client : vérifier qu'elle y est autorisée | Loi art. 17 j) | Exécutant identifié sur chaque opération, contrôlé contre les mandataires |
| Client occasionnel : pas de compte ; opérations liées considérées ensemble | Loi art. 2 §20 | `clients.nature_relation = occasionnel`, empreinte aussi calculée pour lui |
| Système d'information : profilage, filtrage temps réel, suivi et alertes, solde global d'un même client, recensement des opérations d'un même client, détection des opérations suspectes, revue annuelle | Instr. 001 art. 6 | Chaque exigence a une fonctionnalité et un test ; matrice dans `docs/CONFORMITE.md` |
| Protection des données | Loi art. 12 h) ; code du numérique du pays (Bénin : Loi n°2017-20, APDP — à confirmer par l'équipe) | Chiffrement des champs d'identité, index aveugles, journal des consultations, minimisation au niveau 2 |
| L'outil recommande, l'humain décide | Responsabilité de la structure LBC/FT (Instr. 001 art. 8) | Aucune clôture d'alerte, gel définitif, DOS ou refus sans décision d'un agent habilité, journalisée |

**Sources de valeur** (colonne `source`, obligatoire sur tout paramètre, règle, seuil, liste de référence) :
- `reglementaire` : valeur écrite dans un texte présent dans `docs/sources/` (article cité).
- `briefing_cif` : valeur annoncée oralement par les experts CIF (transcription), à confirmer.
- `politique_interne` : choix de l'institution, modifiable par l'admin.
- `demo` : hypothèse de démonstration, à valider. L'interface affiche un badge visible.

## 4. Stack imposée

- PHP ≥ 8.3, Laravel 12.x (vérifier la version réellement installée et adapter la syntaxe ; ne rien supposer).
- Base centrale : PostgreSQL. Nœud d'agence et tests : SQLite. **Toutes les migrations doivent fonctionner sur les deux.**
- Front : Blade + Alpine.js + Tailwind (Vite, assets compilés, zéro CDN). Pas de Livewire, pas de Filament, pas de starter kit basé sur `User`.
- Files d'attente, cache, sessions : driver `database` (pas de Redis).
- Paquets autorisés : `laravel/sanctum`, `spatie/laravel-permission`, `pragmarx/google2fa`, `bacon/bacon-qr-code`, `league/csv`, `barryvdh/laravel-dompdf`, `larastan/larastan` (dev), `laravel/pint` (dev). Côté npm : `alpinejs`, `chart.js`, `html5-qrcode`. Toute autre dépendance → `docs/DECISIONS.md` d'abord.
- Extensions PHP à ne pas exiger : `intl`, `gmp`, `redis`. Normalisation via `Str::ascii`.

## 5. Conventions de code

**Nommage**
- Domaine en français, sans accents : tables au pluriel snake_case (`reseaux`, `personnes_physiques`), modèles au singulier (`Reseau`, `PersonnePhysique`).
- **Toujours déclarer `protected $table`** (Eloquent pluraliserait en anglais).
- Aucune table `users`, aucun modèle `User`. Tables séparées : `admins`, `agents`, `systemes_externes`, `clients`.

**Base de données**
- Entités métier et synchronisées : clé primaire UUID (`HasUuids`). Tables de configuration : `id` auto-incrémenté.
- Pas de colonnes `enum` SQL : colonne `string` + enum PHP « backed » dans `app/Enums/` + cast.
- Montants : `decimal(20,2)` + `devise_code` (ISO 4217, `XOF` par défaut). Dates stockées en UTC, affichées dans le fuseau du pays.
- `json` (jamais `jsonb`), `binary` pour les vecteurs, aucune requête SQL propre à PostgreSQL sans alternative SQLite.
- Toute nouvelle migration hors socle : préfixe horaire réservé (`..._1xxxxx_n1_...` pour le niveau 1, `..._2xxxxx_n2_...` pour le niveau 2). Ne jamais modifier une migration du socle déjà fusionnée : créer une migration additive.

**Données personnelles**
- Tout champ d'identité est chiffré via le cast `App\Casts\Chiffre` (clé par réseau, jamais `APP_KEY`). Recherche exacte uniquement via colonne `*_idx` (index aveugle HMAC).
- Recherche approchée par nom : **uniquement par empreinte**.
- Interdit : données d'identité dans les logs, les messages d'exception, `alertes.faits`, les payloads de webhooks, les réponses d'API non habilitées.
- Toute consultation d'une fiche client complète est journalisée (`journal_audit`, action `consultation`).

**Architecture**
- Contrôleurs minces : validation par `FormRequest`, autorisation par `Policy` (`$this->authorize(...)`), logique dans `app/Services/<Domaine>/`.
- Contrôleurs rangés par espace puis domaine : `app/Http/Controllers/Admin/<Domaine>/`, `.../Agent/<Domaine>/`, `.../Api/V1/<Domaine>/`.
- Vues rangées en miroir : `resources/views/admin/<domaine>/<ressource>/index.blade.php`.
- Routes : un fichier par domaine dans `routes/admin/`, `routes/agent/`, `routes/api/v1/`, chargés automatiquement. Noms : `admin.<domaine>.<ressource>.<action>`, `agent.<domaine>...`, `api.v1.<domaine>...`. URL en français kebab-case.
- Modèles : `$fillable` explicite (jamais `$guarded = []`), casts typés, relations typées, scopes nommés.
- Communication niveau 1 ↔ niveau 2 : **uniquement** par les événements de `app/Events/` et les interfaces de `app/Contracts/`. Jamais d'appel direct d'un service de l'autre équipe.
- Multi-réseaux : toute requête métier passe par le trait `AppartientAuReseau` (scope global). Toute tâche console reçoit le réseau explicitement.

**Qualité**
- Chaque fonctionnalité livrée avec au moins un test Feature. Tests obligatoires : isolation entre réseaux, non-divulgation au guichet, absence de données d'identité en clair dans la base et dans les logs.
- Avant chaque commit : `vendor/bin/pint` puis `php artisan test`. Aucun commit sur une suite rouge.
- Commits : `type(portee): message` en français. `type` ∈ feat, fix, test, docs, refactor, chore. `portee` ∈ socle, n1, n2, fusion.
- Travailler par petites étapes ; à la fin de chaque étape : résumé de 5 lignes maximum, fichiers touchés, commandes de test lancées.

## 6. Propriété des fichiers (évite les conflits de fusion)

| Zone | Propriétaire | Les autres peuvent… |
|---|---|---|
| `CLAUDE.md`, `bootstrap/`, `config/`, `composer.json`, `package.json`, migrations du socle, `app/Models/`, `app/Enums/`, `app/Contracts/`, `app/Events/`, `app/Casts/`, `app/Data/`, `app/Services/{Securite,Audit,Parametres,Empreinte,Clientele,Operations,Stockage}`, layouts et navigation | Socle (chef d'équipe) | Proposer une modification dans `docs/DEMANDES_SOCLE.md`, appliquée sur `main` puis récupérée par rebase |
| `app/Services/{Kyc,Filtrage,Listes,Ppe,Risque,Detection,Consolidation,Conformite,Explication,Rapports}`, `app/Http/Controllers/Agent/*` sauf `CanalCif`, `app/Http/Controllers/Admin/{Detection,Listes,Ppe,Risque,Rapports}`, `routes/agent/*` sauf `canal-cif.php`, `routes/admin/{detection,listes,ppe,risque,rapports}.php`, `app/Listeners/Intra/`, `app/Providers/IntraServiceProvider.php`, `tests/*/Intra/`, `lang/fr/n1.php` | Niveau 1 | Lire |
| `app/Services/{Interreseaux,Integration,Synchronisation,Webhooks}`, `app/Http/Controllers/Api/*`, `app/Http/Controllers/Admin/{CanalCif,Integrations,Noeuds}`, `app/Http/Controllers/Agent/CanalCif`, `routes/api/v1/*`, `routes/admin/{canal-cif,integrations,noeuds}.php`, `routes/agent/canal-cif.php`, `app/Listeners/Interreseaux/`, `app/Providers/InterreseauxServiceProvider.php`, `tests/*/Interreseaux/`, `lang/fr/n2.php`, `docs/api/`, `public/manifest.webmanifest`, `resources/js/pwa/` | Niveau 2 | Lire |
| Seeders et scénarios de démonstration finaux, `docs/GRILLE_JURY.md`, README final | Fusion | — |

## 7. Commandes de référence

```bash
composer run dev                      # serveur + queue + vite (Laravel 12)
php artisan migrate:fresh --seed      # socle + référentiels
php artisan demo:generer --graine=2026
php artisan demo:scenario A           # rejoue un scénario de pitch
php artisan test --parallel
vendor/bin/pint && vendor/bin/phpstan analyse
php artisan audit:verifier-chaine
```

## 8. Quand une information manque

Ne pas supposer. Écrire la question dans `docs/DECISIONS.md` (contexte, options, recommandation), implémenter l'option la plus prudente derrière un paramètre avec `source = demo`, et continuer.

## 9. Glossaire minimal

SFD : système financier décentralisé. RLBC/FT : responsable de la lutte contre le blanchiment. CENTIF : cellule nationale de traitement des informations financières. DOS : déclaration d'opération suspecte. DTE : déclaration des transactions en espèces (Loi art. 72). PPE : personne politiquement exposée (Loi art. 2 §50 : nationale, étrangère, organisation internationale, famille, proches associés). BE : bénéficiaire effectif. NPI : numéro personnel d'identification. CLK : empreinte d'enregistrement combinant plusieurs champs dans un seul filtre de Bloom. BLIP : bruit aléatoire contrôlé ajouté aux bits d'une empreinte pour limiter la réidentification.
