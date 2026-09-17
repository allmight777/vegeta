# Décisions — CIF-Empreinte

Journal des choix pris quand une information manquait ou quand `05_PROMPT_MVP_RECENTRE.md`
laissait une marge d'interprétation, conformément à `CLAUDE.md` §8 (« ne pas supposer, écrire la
question, implémenter l'option la plus prudente, continuer »). Classé par thème, le plus
structurant en premier.

## 0. Information manquante à l'origine : `docs/sources/` absent

Aucun texte réglementaire (Loi uniforme, Instruction BCEAO 001-03-2025, Instruction 003-03-2025,
Décision n°021/2023/CM/UMOA) n'a été fourni à la session ayant construit ce dépôt. Tous les
seuils réglementaires cités dans le code (KYC, CENTIF) proviennent donc soit d'une citation faite
dans les prompts eux-mêmes (`briefing_cif`), soit d'une hypothèse de démonstration (`demo`) —
**jamais** d'une lecture directe d'un texte. Chaque valeur concernée porte sa `source` et un badge
visible dans l'interface (`x-badge-source`). Rien n'est marqué `reglementaire` dans ce dépôt sans
un texte réellement présent pour le justifier.

## 1. Périmètre : MVP resserré plutôt que socle + niveau 1 + niveau 2

Choix de l'utilisateur en amont du build (voir conversation) : `05_PROMPT_MVP_RECENTRE.md` suivi
intégralement plutôt que `01_PROMPT_SOCLE.md` + `02_PROMPT_NIVEAU_1_INTRA.md`. Une seule
application Laravel, un seul niveau d'accès aux données (pas de canal inter-réseaux réel), pas de
nœud d'agence hors ligne synchronisé (les deux sont simulés, §8 du prompt resserré).

## 2. Stack : écarts par rapport à `CLAUDE.md` (déjà annoncés par le prompt resserré, §3)

- **Laravel 12, pas 11** : le squelette initial du dépôt contenait Laravel 11.56. Mis à niveau en
  place (`composer require laravel/framework:^12.0 --with-all-dependencies`) avant toute autre
  écriture de code, pour respecter la stack imposée.
- **SQLite uniquement**, comme demandé par le prompt resserré (pas de PostgreSQL testé dans ce
  dépôt). Les migrations restent portables (types standards, pas de SQL propre à un moteur).
- **Paquets non installés** car hors du périmètre resserré : `laravel/sanctum` (pas d'API réelle
  dans ce MVP), `spatie/laravel-permission` (rôles = simple colonne `role` sur `agents`, vérifiée
  par une Policy et un middleware dédié, pas par un système de permissions à granularité fine),
  `pragmarx/google2fa` et `bacon/bacon-qr-code` (pas de 2FA dans ce MVP), `html5-qrcode` (pas de
  scan QR). `laravel/pint` conservé. `league/csv` et `barryvdh/laravel-dompdf` ajoutés (import CSV,
  PDF de la déclaration CENTIF). npm : `alpinejs`, `chart.js` ajoutés ; Tailwind déjà présent.

## 3. Chiffrement des données d'identité — simplifié par rapport au socle complet

`CLAUDE.md` §5 exige le chiffrement des champs d'identité via un cast dédié, avec une clé par
réseau. Le socle complet (`01_PROMPT_SOCLE.md` étape 7) prévoit en plus une table
`cles_cryptographiques` avec KCV, rotation de version, et des données associées authentifiées
(AAD) liant un chiffré à sa ligne. Pour ce MVP resserré, simplification assumée :

- **Une seule clé de base pour toute l'application** (`CLE_CIF_DEMO`, 32 octets aléatoires en
  base64, jamais `APP_KEY`), dont on dérive une sous-clé par usage via HMAC
  (`App\Services\Securite\GestionnaireCles::cle(string $usage)`) — `chiffrement_donnees`,
  `index_aveugle`, `empreinte`, `blocage`. Pas de clé par réseau, pas de table de métadonnées de
  clé, pas de commande `cles:generer`/`cles:rotation`.
- Pas d'AAD (donnée associée authentifiée liant le chiffré à `table|colonne|id`) : un chiffré
  déplacé par une requête SQL brute vers une autre ligne ne serait pas détecté par le
  déchiffrement lui-même (seul l'audit applicatif protège contre ça dans ce MVP).
- Justification : construire l'infrastructure multi-clé complète du socle est un chantier en soi,
  hors du temps disponible pour un MVP de démonstration ; le chiffrement AES-256-GCM réel avec clé
  jamais égale à `APP_KEY` — l'exigence testable de `CLAUDE.md` — est respecté.

Implémentation : `App\Casts\Chiffre` (scalaire) et `App\Casts\ChiffreIndexe` (scalaire + colonne
d'index aveugle associée, en un seul cast — pratique Laravel : un cast personnalisé peut écrire
plusieurs colonnes depuis `set()`).

## 4. Champs stockés en clair, chiffrés, ou en index aveugle seul

Le schéma compact de `05_PROMPT_MVP_RECENTRE.md` §4 ne liste pas explicitement un « chiffré » en
face de chaque champ. Règle appliquée pour trancher, par cohérence avec le motif déjà explicite du
prompt (`nom chiffré, nom_idx`, `npi_idx` seul) :

- **Chiffré + index aveugle** (valeur réaffichée à l'écran) : `nom`/`prenoms` (personnes
  physiques), `date_naissance`, `lieu_naissance`, `piece_identite_numero`, `adresse`
  (texte simple, pas de structure JSON), `raison_sociale` (personnes morales), `rccm`, `ifu`
  (les deux sont dans le formulaire de complétude §5.1, donc réaffichés), `matricule` (agents),
  `email` (admins), `numero` (comptes, affiché sur chaque écran d'opération).
- **Index aveugle seul, aucune valeur en clair conservée** : NPI (`npi_idx`) — sert uniquement au
  rapprochement de doublons à l'import, jamais réaffiché dans un écran de ce MVP ; c'est la
  lecture littérale du prompt qui ne mentionne que `npi_idx` sans champ `npi` associé.
- **Non chiffré** : `profession`, `revenus_mensuels_estimes` (donnée financière, pas une donnée
  d'identité au sens de `CLAUDE.md` §5), `forme_juridique`, noms des administrateurs/agents
  (identité de compte applicatif interne, pas la clientèle visée par la loi).
- Colonnes KYC ajoutées au-delà du schéma compact du §4 (`lieu_naissance`,
  `piece_identite_numero`, `piece_identite_expiration`, `adresse`, `profession`,
  `revenus_mensuels_estimes` sur `personnes_physiques` ; `forme_juridique`, `date_creation`,
  `rccm`, `ifu` sur `personnes_morales`) : nécessaires pour rendre implémentable le référentiel
  `config/champs_kyc_obligatoires.php` explicitement demandé par §5.1, qui les cite comme champs
  à compléter — considéré comme un complément fidèle à l'esprit du prompt, pas un écart.

## 5. Moteur d'empreinte : `empreinte_combinee` et blocage MinHash

- `empreinte_combinee` est implémentée comme la **concaténation** des vecteurs nom (1000 bits) et
  date de naissance (500 bits) en un seul vecteur de 1500 bits (`App\Support\Bitset::concatener`),
  comparé par un unique calcul de Dice. Interprétation choisie car §4 décrit `empreinte_combinee`
  comme « (nom + date de naissance) » sans détailler le mécanisme ; la concaténation pondère
  naturellement le nom ~67 % / la date ~33 %, proche du score composite 0.7/0.3 documenté ailleurs
  (§6.1) pour la comparaison séparée nom vs date.
- Le blocage MinHash (20 bandes × 3 hachages, §6.1) est **implémenté et testé**
  (`App\Services\Empreinte\IndexBlocageMinHash`) mais **non branché** dans le chemin chaud du
  filtrage ni de la détection de fractionnement. Au volume de démonstration (quelques centaines à
  quelques milliers d'enregistrements), la comparaison Dice directe sur toutes les entrées reste
  largement sous le temps cible ; le brancher deviendrait nécessaire à volumétrie réelle. Le
  composant reste utilisable tel quel pour un futur import de masse.

## 6. Authentification et rôles

- Deux gardes (`admin`, `agent`), comme `CLAUDE.md` §11, mais sans 2FA (hors périmètre resserré,
  voir §2 ci-dessus) et sans verrouillage de compte après échecs répétés (`tentatives_echouees`/
  `verrouille_jusqu_au` du socle complet) — seule une limitation de débit
  (`RateLimiter::for('connexion', 5/minute par identifiant+IP)`) protège la connexion.
- Pas de réinitialisation de mot de passe par e-mail : un admin réseau désactive/recrée un compte
  agent depuis l'écran de gestion (non construit dans ce MVP faute de temps ; les comptes de démo
  sont recréés par le seeder).
- Rôles agent limités aux trois du prompt resserré (`guichet`, `responsable_lbcft`, `direction`),
  vérifiés par un middleware dédié (`role.agent:...`) et par les `Policy` Laravel standard — pas
  de table de permissions à granularité fine comme le socle complet (`clients.voir_identite_complete`,
  etc.).

## 7. `ContexteReseau` simplifié

Le socle complet prévoit un singleton + scope Eloquent global (`AppartientAuReseau`) filtrant
automatiquement toute requête. Pour ce MVP, `App\Services\Contexte\ContexteReseau::reseauId()`
est un accesseur explicite, appelé dans chaque contrôleur qui doit filtrer par réseau
(`Client::where('reseau_id', $contexte->reseauId())`). Plus simple, plus explicite dans les
contrôleurs, mais demande une discipline manuelle (pas de garde-fou automatique si un contrôleur
oublie l'appel).

## 8. Formatage des montants — pas d'extension `intl`

`CLAUDE.md` §4 interdit d'exiger l'extension PHP `intl`. Le formatage des montants dans les
explications générées (`App\Services\Detection\DetecteurFractionnement`) et les vues utilise donc
un séparateur de milliers manuel (`number_format`), jamais `Illuminate\Support\Number::format`
(qui requiert `intl`).

## 9. Seuils de détection — tous `source = demo` sauf le seuil CENTIF

- `SEUIL_MENSUEL_CENTIF` = 15 000 000 XOF, `source = briefing_cif`, avec la mention explicite du
  prompt : « à confirmer dans la Décision n°021/2023/CM/UMOA » (texte non fourni à ce dépôt).
- Les trois autres règles (`FRACTIONNEMENT_GUICHET`, `FRACTIONNEMENT_MULTI_AGENCES`,
  `COMPTE_DORMANT_REACTIVE`) ont des seuils `source = demo`, à calibrer avec les mentors métier —
  aucune valeur de ces trois règles n'est citée dans un texte ou un briefing fourni à ce dépôt.

## 10. Listes de sanctions — aucune liste ONU réelle importée

`docs/sources/` ne contient pas la liste consolidée ONU (fichier XML) pour cette instance du
dépôt. Aucune entrée n'est donc créée avec `source = onu` par les seeders : `ListesDemoSeeder` ne
crée que des entrées `source = demo` (extraits fictifs façon ONU) et `source = ppe_benin` /
`ppe_cedeao` (PPE fictives, conformément au prompt qui les demande explicitement fictives). La
commande `listes:importer --source=onu --fichier=...` existe et fonctionnerait avec un vrai
fichier CSV `nom,categorie` le jour où l'équipe le fournit ; le format XML natif de la liste ONU
n'a pas été implémenté faute de fichier de référence à valider dessus (`docs/donnees/
FORMAT_LISTE_ONU.md`, prévu par le guide d'équipe, n'a pas pu être rédigé pour la même raison).

## 11. `demo:scenario` — numérotation 1 à 6, pas de lettres A/C/D/F/G

Le prompt resserré (§10) demande une commande rejouable par problème sans imposer de nommage. Les
scénarios lettrés A/C/D/F/G viennent de l'ancien découpage socle/niveau 1 (non suivi, voir §1) ;
ce dépôt utilise `php artisan demo:scenario {1..6}`, un code par problème (§2 du prompt resserré).

## 12. Simulation du niveau 2 (`demo:publier-signal`)

Aucune table de « signal » n'existe dans le schéma resserré (§4) — volontairement, puisque §8 du
prompt précise que le niveau 2 est simulé, pas construit. La commande `demo:publier-signal`
affiche donc en console ce qui serait publié (type de risque, extrait d'empreinte, réseau
émetteur) sans rien persister ni exposer d'identité, plutôt que d'inventer une table hors
périmètre.
