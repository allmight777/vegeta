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

- PHP ≥ 8.3 avec `pdo_mysql` (connexion par défaut de ce poste), `pdo_sqlite` (suite de tests et
  nœud d'agence hors ligne), `openssl`, `mbstring`, `fileinfo` (aucune extension `intl`, `gmp`
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

Ce poste de développement utilise **MySQL/MariaDB** en local (`DB_CONNECTION=mysql` dans `.env`,
raison documentée dans `docs/DECISIONS.md`) — créer la base avant la première migration :

```sql
CREATE DATABASE CIF CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

SQLite reste utilisable (mettre `DB_CONNECTION=sqlite` dans `.env` et `touch database/database.sqlite`)
et c'est ce que la suite de tests utilise systématiquement (`phpunit.xml`, base en mémoire), quelle
que soit la connexion par défaut de l'application.

## Installation

```bash
composer install
npm install
cp .env.example .env
php artisan key:generate
# Créer la base MySQL "CIF" (commande ci-dessus) avant cette étape
php artisan migrate:fresh --seed   # crée les référentiels + comptes et données de démonstration
npm run build                      # ou `npm run dev` en développement
php artisan serve
```

Le seeder `CLE_CIF_DEMO` (clé de chiffrement de démonstration) est déjà présente dans
`.env.example` — **à régénérer avant toute mise en production** (`openssl rand -base64 32`),
jamais commitée en clair ailleurs qu'en local.

## Comptes de démonstration

Générés (mots de passe aléatoires) par `DemoComptesSeeder`, affichés une seule fois en console
lors du `migrate:fresh --seed`. Relancer le seeder si les identifiants ont été perdus :

```bash
php artisan db:seed --class=DemoComptesSeeder
```

- Espace admin (`/admin/connexion`) : un admin plateforme (aucun accès à la clientèle) et un admin
  du réseau de démonstration « Alpha ».
- Espace agent (`/connexion`) : un agent par rôle (`guichet`, `responsable_lbcft`, `direction`),
  tous rattachés à l'agence de Dassa du réseau Alpha.

## Rejouer les scénarios de démonstration

```bash
php artisan demo:scenario 1   # problème 1 — complétude KYC après import core banking
php artisan demo:scenario 2   # problème 2 — filtrage sanctions/PPE, explication en langage humain
php artisan demo:scenario 3   # problème 3 — fractionnement entre deux agences
php artisan demo:scenario 4   # problème 4 — signataire de personne morale filtré individuellement
php artisan demo:scenario 5   # problème 5 — tableau de bord conformité
php artisan demo:scenario 6   # problème 6 — non-divulgation au guichet (art. 63)
php artisan demo:publier-signal   # simulation du signal inter-réseaux (niveau 2, voir ci-dessus)
```

Chaque scénario est idempotent (rejouable plusieurs fois pendant une répétition de pitch).

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

## Commandes utiles

```bash
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
