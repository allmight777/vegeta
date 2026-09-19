# CIF-Empreinte

Couche de conformité LBC/FT/FP pour les systèmes financiers décentralisés (SFD) d'Afrique de
l'Ouest — projet DigiCoop-WA+, Hackathon National d'Innovation CIF, Bénin, septembre 2026.

CIF-Empreinte ne remplace pas un core banking : elle importe/complète les fiches KYC, filtre les
clients et opérations contre les listes de sanctions et les profils PPE, détecte le fractionnement
d'opérations (y compris entre agences), et donne à la conformité un tableau de bord actionnable —
tout en garantissant qu'un agent de guichet ne voit jamais qu'une vérification est en cours (Loi
uniforme LBC/FT/FP, art. 63).

L'originalité technique : chaque identité est transformée en **empreinte** (normalisation →
bigrammes → HMAC-SHA256 avec clé secrète → filtre de Bloom). Deux variantes orthographiques d'un
même nom restent proches ; aucune empreinte ne permet de retrouver le nom original.

## Ce qui est réel, ce qui est simulé

| | Réel | Simulé |
|---|---|---|
| Chiffrement, empreinte, filtrage, détection | Oui — AES-256-GCM, HMAC-SHA256, calculs en PHP natif, aucun appel réseau | — |
| Mode hors ligne | Oui — l'application tourne entièrement en local (SQLite, aucune dépendance externe au runtime) | — |
| Inter-réseaux (niveau 2) | — | Deux réseaux dans la même base ; `php artisan demo:publier-signal` illustre le principe sans canal réel ni donnée d'identité transmise |
| Nœud d'agence synchronisé | — | Non construit ; le mode hors ligne de l'agence est déjà démontré par le point ci-dessus |
| Liste de sanctions ONU | — | Aucun fichier officiel fourni à ce dépôt : les entrées de démonstration sont marquées `source = demo`, jamais présentées comme réelles |
| Déclaration CENTIF (PDF) | Génère un vrai PDF | Gabarit de démonstration — le formulaire officiel CENTIF (fixé par arrêté) n'a pas été fourni à l'équipe |
| Assistant IA conformité | Peut appeler une vraie API de complétion de chat si `.env` renseigné | Recommandé pour la démo : simulateur local par mots-clés (`resources/assistance/*.md`), aucune dépendance, aucun appel réseau |

Voir `docs/DECISIONS.md` pour le détail de chaque simplification assumée par rapport au cahier des
charges complet (`CLAUDE.md`, `docs/prompts/`).

## Prérequis

- PHP ≥ 8.3 avec `pdo_sqlite` (base de données par défaut, suite de tests, nœud d'agence hors
  ligne), `openssl`, `mbstring`, `fileinfo` (aucune extension `intl`, `gmp`
  ou `redis` requise).
- Composer 2, Node.js ≥ 18 (npm).
- Fonctionne sous Linux, macOS et Windows (ex. Laragon, Herd).
- **OCR local des documents scannés/photographiés** (mode dégradé, `07_PROMPT_MODE_DEGRADE_NPI_OCR`)
  — deux dépendances **système**, à installer en plus de `composer install` :
  - Binaire **Tesseract OCR** + paquet de langue **français (`fra`)**.
    Windows : installeur officiel https://github.com/UB-Mannheim/tesseract/wiki (cocher le
    composant de langue française à l'installation), puis ajouter le dossier d'installation
    (ex. `C:\Program Files\Tesseract-OCR`) à la variable d'environnement `PATH`.
    Linux (Debian/Ubuntu) : `sudo apt-get install tesseract-ocr tesseract-ocr-fra`.
  - **Ghostscript** + extension PHP **`imagick`** (nécessaires uniquement pour rasteriser un
    PDF scanné avant OCR — pas pour l'OCR direct d'une photo `.jpg`/`.png`).
    Windows : installeur Ghostscript https://ghostscript.com/releases/gsdnld.html, puis activer
    `extension=imagick` dans `php.ini` (DLL PECL correspondant à la version de PHP utilisée sous
    Laragon/Herd, à redémarrer après activation).
  - Sans ces deux dépendances, le reste de l'application fonctionne normalement : seule
    l'extraction OCR échoue avec un message explicite (jamais une erreur silencieuse) — voir
    `docs/COMPOSANTS_TIERS.md`.

## Base de données locale

L'application utilise **SQLite** par défaut : un seul fichier (`database/database.sqlite`), aucun
service à démarrer, aucun identifiant à configurer. `.env.example` contient déjà
`DB_CONNECTION=sqlite`. Le fichier est créé à l'étape d'installation ci-dessous ; il n'est pas
versionné. Les migrations restent compatibles PostgreSQL et MySQL : pour y revenir, décommenter
les variables `DB_*` correspondantes dans `.env.example`.

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate          # APP_KEY (sessions, cookies) — obligatoire avant les tests
touch database/database.sqlite     # Windows PowerShell : New-Item database/database.sqlite
php artisan migrate:fresh --seed   # crée les référentiels + comptes et données de démonstration
npm run build                      # ou `npm run dev` en développement
php artisan serve
```

Vérifier que tout est en place avant de démarrer (aucun effet de bord, lecture seule) :

```bash
php artisan installation:verifier
```

La commande contrôle `APP_KEY`, `CLE_CIF_DEMO`, les extensions PHP requises, la base
SQLite et les données de démonstration, puis affiche un tableau vert/rouge avec la
commande exacte à lancer pour chaque point manquant.

**Deux clés distinctes, ne pas les confondre :**

- `APP_KEY` — clé Laravel standard, générée par `php artisan key:generate`. Sans elle,
  la suite de tests échoue en bloc (`MissingAppKeyException`).
- `CLE_CIF_DEMO` — clé métier de CIF-Empreinte, dont sont dérivées par HMAC les sous-clés
  de chiffrement AES-256-GCM, d'index aveugle et d'empreinte. Une valeur de démonstration
  est fournie dans `.env.example` pour que l'installation fonctionne immédiatement ;
  elle est publique et **doit être régénérée avant toute mise en production**
  (`openssl rand -base64 32`). Changer cette clé invalide les empreintes et les index
  aveugles déjà calculés : rejouer `migrate:fresh --seed` après changement.

## Mode démonstration (jury)

Dans `.env`, la démonstration hors ligne repose sur trois réglages : `ASSISTANCE_IA_FORCER_SIMULATEUR=true`
et `RECHERCHE_WEB_FORCER_SIMULATEUR=true` (assistant et recherche web répondent par leurs simulateurs
locaux, sans wifi), et `MAIL_MAILER=log` (aucun e-mail envoyé). Mettre aussi `APP_DEBUG=false` pour
qu'aucune trace technique ne s'affiche à l'écran. Pour utiliser l'IA en ligne (Gemini), renseigner
`ASSISTANCE_IA_API_URL`, `ASSISTANCE_IA_API_CLE`, `ASSISTANCE_IA_MODELES` (exemple dans `.env.example`)
et repasser les deux `FORCER_SIMULATEUR` à `false`. Ne jamais committer de clé dans `.env.example`.

## Comptes de démonstration

Générés (mots de passe aléatoires) par `DemoComptesSeeder`, affichés une seule fois en console
lors du `migrate:fresh --seed`. Relancer le seeder si les identifiants ont été perdus :

```bash
php artisan db:seed --class=DemoComptesSeeder
```

- Espace admin (`/admin/connexion`) : un admin plateforme (aucun accès à la clientèle) et un admin
  du réseau de démonstration « Alpha ».
- Espace agent (`/connexion`) : un caissier (`CAI-0001`) et un responsable d'agence (`RES-0001`),
  tous deux rattachés à l'agence de Dassa du réseau Alpha.

## Rejouer les scénarios de démonstration

```bash
php artisan demo:scenario 1   # problème 1 — complétude KYC après import core banking
php artisan demo:scenario 2   # problème 2 — filtrage sanctions/PPE, explication en langage humain
php artisan demo:scenario 3   # problème 3 — fractionnement entre deux agences
php artisan demo:scenario 4   # problème 4 — signataire de personne morale filtré individuellement
php artisan demo:scenario 5   # problème 5 — tableau de bord conformité
php artisan demo:scenario 6   # problème 6 — non-divulgation au guichet (art. 63)
php artisan demo:scenario 8   # problème 7 — incohérence profil déclaré / opérations observées
php artisan demo:publier-signal   # simulation du signal inter-réseaux (niveau 2, voir ci-dessus)
```

Chaque scénario est idempotent (rejouable plusieurs fois pendant une répétition de pitch).

## Parcours de démonstration — fonctionnalités IA

Conformément à `docs/prompts/13_PROMPT_IA_VISIBLE_DANS_INTERFACE.md` : une fonctionnalité IA qui ne
se voit pas et ne se déclenche pas depuis le navigateur ne compte pas comme livrée. Chemins vérifiés
manuellement (navigateur réel / requêtes HTTP authentifiées contre le serveur réellement démarré,
pas seulement la suite de tests) après un simple `migrate:fresh --seed`, sans aucune commande
supplémentaire.

**Mémoire de décisions de filtrage** (`12_PROMPT_IA_INTEGREE_PROFONDE.md` §6) :

> Se connecter en responsable d'agence (`RES-0001`) → menu « Filtrage » → la correspondance
> « AHOUANDJINOU Rachidatou » est déjà affichée, avec l'encart vert « 3 cas similaires déjà
> tranchés dans votre réseau — motif dominant : Homonyme — nom courant, aucun autre élément
> concordant (2) » directement sous son nom, sans avoir cliqué sur quoi que ce soit.
> Cliquer sur « Autre » dans le motif de décision → taper un texte de justification dans le champ
> qui apparaît → quitter le champ (perte du focus) : en simulateur (hors connexion, mode
> recommandé pour la démonstration), rien ne s'affiche — c'est le comportement attendu, pas un
> bug (voir « Comportement hors connexion » ci-dessous). Avec une clé Gemini configurée et une
> connexion active, un encart violet « Ça ressemble à … — Utiliser ce motif » apparaît à la place.

**Bibliothèque documentaire / recherche documentaire de l'assistant** (`10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA.md`) :

> Se connecter en admin (`admin.alpha@cif-empreinte.demo`) → menu « Documents IA » → le document
> « typologies-demo » apparaît déjà dans la liste, statut « Extraction réussie ».

### Comportement hors connexion (§2.5)

Le mode recommandé pour la démonstration (`IA_MODE_DEMONSTRATION=true`, ou simplement aucune clé
API configurée) force le simulateur local — vérifié pour chaque fonctionnalité IA :

- **Assistant conformité** (les trois espaces) : répond toujours quelque chose, jamais un écran
  vide ni une erreur brute — soit une réponse trouvée par mots-clés dans la base de connaissances
  locale, soit le message clair « Je n'ai pas d'information là-dessus dans ma base de
  connaissances. » avec l'option d'escalader vers le responsable.
- **Mémoire de décisions — comptage des cas similaires** : ne dépend d'aucune IA (requête SQL
  simple), fonctionne à l'identique en ligne et hors ligne.
- **Mémoire de décisions — suggestion de motif** : simple amélioration ergonomique, jamais
  démo-critique. Hors connexion, l'encart de suggestion ne s'affiche tout simplement pas (aucun
  message d'erreur, aucun indicateur de chargement qui tourne dans le vide) — le responsable
  choisit son motif normalement, comme si la fonctionnalité n'existait pas.

### L'IA dans les trois espaces (16_PROMPT) — tout fonctionne en mode simulateur, hors connexion

Comptes de démonstration : voir `migrate:fresh --seed` (mots de passe affichés une seule fois).

| Où | Quoi | Chemin de clics | Vérifié au navigateur |
|---|---|---|---|
| Caissier | Alerte doublon par empreinte (jamais le nom du dossier, seulement l'agence) | `CAI-0001` → Clients → Nouveau client → Prénoms « Kofi », Nom « ADJAO » → quitter le champ : bandeau jaune « Un dossier très proche existe déjà… (Agence de Savalou) » | ✅ Chrome 144 |
| Caissier | Incohérences de profil (retraité à 25 ans, dépôt/revenu, pièce expirée) | Même formulaire : date de naissance 10/04/2001, profession « Retraité », revenus 60000, dépôt espèces 5000000, expiration 15/01/2024 → trois avertissements « contrôle local » ; ou Clients → dossier **TCHOKPON Sylvain** → Compléter (affichés dès l'ouverture) | ✅ Chrome 144 |
| Caissier | Normalisation d'activité | Profession : « commercante » → « 5 dossiers de ce réseau utilisent « Commerçante » » → bouton « Utiliser cette orthographe » | ✅ Chrome 144 |
| Responsable | Mémoire de décisions | `RES-0001` → Filtrage PPE / sanctions → encart vert « 3 cas similaires déjà tranchés… » (ou « Aucun cas similaire tranché pour l'instant ») | ✅ Chrome 144 |
| Responsable | Suggestion de motif | Filtrage → motif « Autre » → texte libre (≥ 10 caractères) → quitter le champ. En mode simulateur : aucune suggestion, aucun élément vide | ✅ Chrome 144 (absence propre) |
| Responsable | Bouton « Expliquer » | Filtrage : sous la correspondance ; Tableau de bord : sur chaque alerte (texte replié à 2 lignes, « Expliquer » le déplie) | ✅ Chrome 144 |
| Admin | Résumé du jour | `admin.alpha@…` → Tableau de bord → encart « Résumé du jour » (dossiers, alertes, documents) avec liens | ✅ Chrome 144 |
| Admin | Usage des documents | Documents IA → colonne « Utilisation » (« Pas encore utilisé » → « Utilisé N fois » après une question sur les typologies posée à l'assistant) | ✅ Chrome 144 |
| Trois espaces | Widget assistant : « salut », « cc », « merci », « qui es-tu ? », « quels sont les profils incomplets ? » (caissier), « combien d'alertes aujourd'hui ? » (responsable) | Bouton flottant jaune en bas à droite → saisir la question | ✅ Chrome 144 |

Nature de la vérification navigateur : Chrome réel piloté par script (protocole DevTools), avec
captures d'écran. Le `blur` des champs est déclenché par événement (un Chrome sans fenêtre n'a pas
de vrai focus clavier) ; à refaire une fois à la main sur la machine de démonstration.

### Configuration de l'identité du système (admin)

Chemin de clics : se connecter sur `/admin/connexion` → menu **Configuration** (barre latérale) →
modifier le nom, le sous-titre, les logos (PNG/JPG/SVG, 2 Mo) ou les couleurs (aperçu en direct
en haut de page) → **Enregistrer** : le changement est visible immédiatement sur les trois espaces,
les pages de connexion, les e-mails et les PDF. Le bouton **Rétablir les valeurs par défaut**
(bas de page) remet l'identité d'origine en un clic. Chaque modification est tracée dans le
journal d'audit (`configuration_systeme_modifiee`, noms des champs seulement). La ligne de
configuration est créée par `migrate:fresh --seed` ; si elle ou la table manque, l'application
utilise les valeurs d'origine sans erreur.

### Audit des fonctionnalités IA déjà livrées

| Fonctionnalité | Point d'entrée visible | Chemin de clics vérifié | Données de démo présentes | Comportement hors ligne vérifié |
|---|---|---|---|---|
| Assistant conformité (caissier/responsable/admin) | ✅ bouton flottant toujours visible, les 3 layouts | ✅ | n/a (répond à la volée) | ✅ message de repli clair |
| Outils par rôle (statistiques, profils incomplets, etc.) | ✅ via le widget assistant | ✅ (structure vérifiée, réponses testées unitairement) | ✅ (clients/alertes de démo déjà seedés) | ✅ (mêmes garanties que l'assistant) |
| Bibliothèque documentaire (admin) | ✅ lien de menu « Documents IA » | ✅ | ✅ (corrigé — 0 document avant, 1 document de démonstration seedé) | ✅ (recherche par mots-clés, aucune IA requise) |
| Escalades caissier → responsable | ✅ lien de menu « Assistance » (espace responsable) | ✅ | état vide légitime (créées en direct pendant la démo) | ✅ état vide explicite (« Aucune question en attente ») |
| Mémoire de décisions (filtrage) | ✅ encart directement dans la file de filtrage | ✅ | ✅ (corrigé — 0 décision avant, 3 décisions passées seedées) | ✅ (comptage 100 % SQL, suggestion dégrade proprement) |

**Bug trouvé en parcourant l'écran** (pas par un test automatisé, exactement le risque que ce
prompt vise à éliminer) : les clients importés par CSV (`ImportateurCsv`) n'avaient pas
`agence_creation_id` renseigné, donc invisibles à `Client::scopeDeLAgence()` — la correspondance
AHOUANDJINOU, pourtant détectée à l'import, n'apparaissait jamais dans la file du responsable.
Corrigé, régression couverte par `tests/Feature/Filtrage/ImportCsvVisibleDansFiltrageTest.php`.

## Structure du dépôt

```
app/Casts/            Chiffre, ChiffreIndexe — chiffrement AES-256-GCM + index aveugle HMAC
app/Console/Commands/  listes:importer, demo:scenario, demo:publier-signal
app/Enums/             tous les statuts/types, string-backed, méthode libelle()
app/Http/Controllers/  Admin/<domaine>/, Agent/<domaine>/ — contrôleurs minces
app/Models/            un modèle par table, $fillable explicite, casts typés
app/Policies/          ClientPolicy (blocage KYC/signataires), rôle guichet restreint par middleware
app/Services/          Empreinte, Filtrage, Detection, Kyc, Import, Explication, Rapports, Securite, Audit
database/migrations/   schéma complet (SQLite ↔ PostgreSQL compatible)
database/seeders/      référentiels + données 100 % synthétiques (aucune donnée personnelle réelle)
docs/                  DECISIONS.md, prompts/ (cahier des charges suivi)
resources/views/       Blade + Tailwind + Alpine, mobile d'abord côté agent
routes/admin/, routes/agent/   un fichier par domaine, autoloadés
tests/                 Feature + Unit, php artisan test
```

## Vigilance constante — cohérence profil déclaré / opérations observées

Le filtrage sanctions/PPE protège l'entrée en relation ; il ne dit rien de ce qui se
passe ensuite. La Loi uniforme (art. 18) et l'Instruction BCEAO 001-03-2025 (art. 6)
imposent de vérifier que les opérations restent cohérentes avec la connaissance qu'on a
du client. Un seuil absolu ne sait pas qu'un dépôt de 400 000 XOF est banal pour un
grossiste et aberrant pour un apprenti tailleur : seul l'écart au profil le dit.

```bash
php artisan coherence:analyser                  # campagne sur tout le parc
php artisan coherence:analyser --reseau=ALPHA   # un seul réseau
php artisan coherence:analyser --simulation     # analyse sans rien écrire
php artisan demo:scenario 8                     # le cas de démonstration
```

**Trois indicateurs déterministes** (`app/Services/Coherence/Indicateurs/`) :

| Indicateur | Ce qu'il observe | Poids |
|---|---|---|
| `EcartFluxRevenus` | cumul des dépôts sur 30 j rapporté au revenu déclaré au KYC | 2 ou 4 |
| `CompteDePassage` | dépôt retiré à ≥ 80 % en moins de 48 h, répété ≥ 3 fois (typologie GIABA) | 3 |
| `IncoherenceActiviteCanal` | activité déclarée incompatible avec les canaux réellement utilisés | 2 |

**Règle cardinale : un indice isolé ne signale rien.** Un agriculteur qui dépose après
la récolte, un commerçant qui encaisse du mobile money : pris séparément, chacun a une
explication banale. Le signalement n'est ouvert qu'à partir de 2 constats concordants
ET d'un poids cumulé de 3 (`config/coherence.php`). Alerter sur chaque indice noierait
la file du responsable en quelques jours, et il cesserait de la lire.

Destinataire : **le responsable d'agence**, jamais le caissier (Loi art. 63,
non-divulgation), comme pour le filtrage.

### L'IA ne note rien

Le score, les seuils et la décision d'alerter sont produits par des règles
déterministes, reproductibles, sans appel réseau. À la question « pourquoi ce membre
a-t-il été signalé ? », la réponse est « parce que ses dépôts valent 50,7 fois son
revenu déclaré et qu'il a fait 12 allers-retours en moins de 20 h » — jamais « parce
que le modèle l'a jugé suspect ». Un signalement LBC/FT non explicable est inopposable
en contrôle.

L'IA n'intervient qu'en surcouche (`NarrateurFaisceau`) : raconter le faisceau, et
proposer les **actions de remédiation KYC** — les questions à poser, les pièces à
réclamer. C'est ce qui distingue un outil qui accuse d'un outil qui fait avancer le
dossier : la mise à jour de fiche qui en découle *est* la vigilance constante attendue
par le régulateur. Sans connexion, le gabarit local produit le même texte.

Tous les seuils sont dans `config/coherence.php`, affichés `source = demo` : ce sont des
hypothèses de démonstration, à calibrer par chaque réseau sur son propre historique.
Ne jamais les présenter comme des seuils réglementaires.

## Refiltrage du parc après publication d'une liste

Le filtrage à la création du dossier ne suffit pas : un membre inscrit sur une liste
*après* son entrée en relation ne repasserait jamais par le moteur. La Loi uniforme
(art. 2 §58) et l'Instruction BCEAO 001-03-2025 (art. 2 §23, art. 6) imposent d'agir
« sans délai », soit 24 heures au maximum après publication.

`listes:importer` enchaîne donc automatiquement le recontrôle de tout le parc :

```bash
php artisan listes:importer onu --fichier=liste.csv --publiee-le="2026-09-19 08:00"
```

```
Import terminé : 2 nouvelle(s) entrée(s) — version IMPORT-20260919044953.

Recontrôle du parc existant contre la liste mise à jour…
12 dossier(s) recontrôlé(s) en 0,02 s (600,0 dossiers/s) — 2 correspondance(s) sur
2 dossier(s). Délai depuis publication de la liste : 3,01 h sur 24 h réglementaires
— échéance respectée.
```

Campagne de rattrapage manuelle :

```bash
php artisan listes:refiltrer                          # tout le parc
php artisan listes:refiltrer --reseau=ALPHA           # un seul réseau
php artisan listes:refiltrer --publiee-le="2026-09-19 08:00"   # mesure du délai
php artisan listes:refiltrer --recalculer-empreintes  # filet de sécurité
```

Chaque campagne est inscrite au journal d'audit chaîné (`refiltrage_parc_execute`)
sans aucune donnée d'identité : c'est la preuve de diligence opposable en contrôle.

### Débit mesuré

Mesures réelles sur SQLite, PHP 8.3 mono-processus, parc synthétique de 2 012 dossiers
contre 507 entrées de liste :

| Régime | Débit | Extrapolation 40 000 membres × 507 entrées |
|---|---|---|
| Nominal (peu de correspondances) | **137 dossiers/s** (~69 500 comparaisons/s) | ~5 minutes |

Extrapolé à la liste consolidée ONU réelle (~25 000 entrées) sur 40 000 membres, soit
1 milliard de comparaisons : **environ 4 heures**, largement sous l'échéance de 24 h —
et sans le blocage MinHash (`IndexBlocageMinHash`, implémenté et testé mais non branché
dans ce MVP), qui réduirait encore l'espace de recherche d'un ordre de grandeur.

## Commandes utiles

```bash
php artisan installation:verifier   # contrôle de pré-vol (à lancer avant toute démo)
php artisan test              # suite complète
vendor/bin/pint                # formatage (obligatoire avant commit)
php artisan route:list         # vérifier les routes des deux espaces
php artisan npi:verifier-en-attente   # rattrapage manuel des NPI en attente de connexion
                                       # (planifiée toutes les 5 min via `php artisan schedule:work`)
```

## Outils d'IA

Ce dépôt a été construit avec [Claude Code](https://claude.com/claude-code) (Anthropic), utilisé
pour générer et relire du code sous supervision de l'équipe. Voir `docs/DECISIONS.md` pour le
détail des choix effectués pendant le build.

L'application elle-même embarque un **assistant IA conformité** (icône en bas d'écran, espaces
agent et admin) pour expliquer le référentiel KYC et guider l'utilisation du produit — jamais pour
prendre une décision de conformité. Sans clé configurée (`ASSISTANCE_IA_API_CLE` dans `.env`), il
répond via un simulateur local par mots-clés (`resources/assistance/*.md`), sans aucune dépendance
à installer ni appel réseau — c'est le mode recommandé pour la démonstration. Voir
`docs/COMPOSANTS_TIERS.md` pour le détail du fournisseur externe optionnel.

## Licence

Code cédé à la CIF dans le cadre du Hackathon National d'Innovation CIF 2026. Dépendances sous
licence permissive uniquement (MIT, BSD, Apache-2.0) — voir `composer.json` / `package.json`.
