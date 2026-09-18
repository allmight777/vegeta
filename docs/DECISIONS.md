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
  agent depuis l'écran de gestion (construit depuis, voir §17 ; les comptes de démo sont recréés
  par le seeder).
- Rôles agent : `caissier` et `responsable_agence` depuis `09_PROMPT_TROIS_PROFILS.md` (fusion des
  trois rôles d'origine `guichet`/`responsable_lbcft`/`direction`, voir §17), vérifiés par un
  middleware dédié (`role.agent:...`) — pas de table de permissions à granularité fine comme le
  socle complet (`clients.voir_identite_complete`, etc.).

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

## 13. `06_PROMPT_FORMULAIRE_CLIENT_ENRICHI` — référentiel, extraction, NPI, système existant

- **Référentiel unique** : `config/champs_fiche_adhesion.php` remplace
  `config/champs_kyc_obligatoires.php` (supprimé), pour ne jamais dupliquer la liste des champs
  entre l'ancien référentiel minimal (9+4 champs) et le nouveau, plus riche, organisé par groupes
  (`App\Services\Kyc\ReferentielFicheAdhesion`). `CalculateurCompletude` et la validation des
  `FormRequest` lisent tous les deux cette même classe.
- **Un seul `FormRequest` par action, pas deux classes par type** : le prompt demandait
  `StockerClientPhysiqueRequest`/`StockerClientMoraleRequest` séparées. Ce dépôt garde le pattern
  déjà en place (`CreerClientRequest`/`CompleterClientRequest` uniques, règles générées
  dynamiquement depuis le référentiel selon `type`) — cohérent avec l'existant, évite deux classes
  presque identiques à maintenir en double.
- **`Mandataire` (personne physique) vs `RoleSignataire::Mandataire` (personne morale)** : ce
  sont deux notions différentes qui coexistent. Un « mandataire désigné » d'une personne physique
  (table `mandataires`, procuration limitée nom/prénoms/lien de parenté) n'a pas les mêmes
  obligations qu'un signataire d'une personne morale ayant le rôle `mandataire` (identité complète,
  filtrage, fiche RLBC/FT) — pas de fusion des deux tables.
- **Fiche RLBC/FT strictement réservée au rôle `responsable_lbcft`** : le prompt dit « visible
  uniquement au rôle responsable_lbcft », sans trancher pour le rôle `direction`. Choix le plus
  prudent conservé à l'époque : seul `responsable_lbcft` voyait et enregistrait cette fiche (ni
  `guichet`, ni `direction`). **Superseded par §17** : `responsable_lbcft` et `direction` ont
  fusionné en `responsable_agence` (`09_PROMPT_TROIS_PROFILS.md`), qui hérite de l'union des deux
  accès — la fiche RLBC/FT est donc désormais visible/modifiable par `responsable_agence` (ni
  `caissier`), toujours vérifié côté serveur par `Agent::estResponsableAgence()`.
- **Extraction de documents** : voir `docs/COMPOSANTS_TIERS.md`. Seule l'extraction `.docx` est
  réellement fonctionnelle (`ZipArchive`, sans nouvelle dépendance). Le mappeur
  (`MappeurChampsExtraits`) est un heuristique regex « libellé : valeur », pas un modèle entraîné —
  confiance fixe (0.9) pour tout champ trouvé par ce motif explicite, cohérent avec l'esprit
  « aucune valeur inventée » de `CLAUDE.md` §3.
- **Connecteur système existant** : adaptation assumée du §7.2 — ce MVP fusionne déjà les lignes
  CSV rapprochées directement dans `clients`/`personnes_physiques`
  (`Services\Import\ImportateurCsv::rapprocherOuCreer`), donc `ConnecteurImportLocal` cherche parmi
  les `PersonnePhysique` déjà persistées (par NPI en index aveugle, sinon par nom + date de
  naissance via l'empreinte), plutôt que de reparser `import_lignes.donnees_brutes`, qui garde des
  en-têtes CSV bruts et variables d'un fichier à l'autre — donc peu fiables comme clé de recherche.
- **NPI jamais stocké en clair** : `personnes_physiques.npi_idx`/`signataires.npi_idx` restent des
  index aveugles seuls (même convention que `docs/DECISIONS.md` §4). `npi_verifie_le` et
  `npi_verification_source` sont de simples métadonnées de traçabilité (date, source du
  vérificateur), jamais le NPI lui-même.
- **`VerificateurNpiApiReel` et `ConnecteurApiCoreBanking`** : écrits (appel HTTP générique
  configurable par `.env`) mais non branchables pour ce hackathon, faute d'accès à une API NPI
  officielle ou à un core banking réel d'un SFD partenaire. `config('kyc.verificateur_npi')` et
  `config('kyc.connecteur_systeme_existant')` restent sur leurs valeurs par défaut `simulateur`/
  `local`.

## 14. `07_PROMPT_MODE_DEGRADE_NPI_OCR` — mode dégradé NPI, OCR local, faux-positifs CSS

- **Faux-positif CSS `NonDivulgationGuichetTest`** : deux occurrences, pas une seule. Le prompt ne
  mentionnait que `.agent-alert-wrapper` (renommée `.agent-alert-conteneur`) ; un second faux
  positif a été trouvé en cours de route (`text-transform: uppercase` contient aussi "ppe").
  Neutralisé par un échappement CSS standard (`up\70 ercase`, rendu identique dans tous les
  navigateurs — `\70` = code hexadécimal du caractère "p"), pour respecter la consigne de ne rien
  changer d'autre dans `layouts/agent.blade.php` (travail de style en cours, non commité) tout en
  faisant réellement passer le test pour la bonne raison.
- **`verifications_npi_en_attente.npi_chiffre`** : exception ciblée et temporaire à la règle
  « index aveugle seul » (`docs/DECISIONS.md` §4). Un rattrapage différé réel doit resoumettre le
  NPI à `VerificateurNpi::verifier()`, ce qu'un hash à sens unique (`npi_idx`) ne permet pas. Le
  NPI est donc chiffré (même cast `Chiffre` que le reste des données d'identité), jamais affiché à
  l'écran, utilisé uniquement par la commande `npi:verifier-en-attente`, et vidé (`null`) dès que
  la ligne quitte l'état `en_attente` — la donnée ne survit pas plus longtemps que nécessaire.
- **Mode dégradé étendu aux signataires** : le schéma du prompt ne mentionnait que `client_id` sur
  `verifications_npi_en_attente`. Une colonne `signataire_id` nullable a été ajoutée (décision
  validée avec l'utilisateur) : sans elle, ajouter un signataire à une personne morale hors
  connexion resterait bloqué à tort, ce qui aurait contredit le principe directeur du prompt
  (« une opération qui dépend d'un service externe ne doit jamais bloquer »).
- **Sémantique de `tentatives`** : compte les passages de la commande planifiée
  (`npi:verifier-en-attente`, toutes les 5 minutes) où la connectivité était **absente** — jamais
  les vérifications réussies. Quand la connexion est présente, la ligne est traitée immédiatement
  (`Traitee`) sans jamais incrémenter `tentatives`. Ce compteur ne mesure donc que la durée
  d'indisponibilité réseau, conformément à l'intention du prompt (« 20 tentatives sur plusieurs
  heures » faute de connexion, pas faute de validation).
- **`SelecteurExtracteurDocument` ne branche pas réellement sur la connectivité** : le prompt
  décrit un aiguillage « hors ligne → OCR local, en ligne → configurable IA/OCR local ». Comme
  aucune implémentation IA n'existe (ni dans `06_PROMPT` ni dans celui-ci), les deux branches
  résolvent aujourd'hui vers `ExtracteurDocumentOcrLocal` — un branchement conditionnel qui ne
  changerait jamais le résultat a été jugé plus trompeur qu'utile (code mort déguisé en logique).
  Le sélecteur ne teste donc que le seul critère qui change réellement le résultat (`.docx` vs le
  reste). Le jour où une implémentation IA existera, `config('extraction.preference_en_ligne')` et
  `Contracts\DetecteurConnectivite` seront réintroduits dans `choisir()` à ce moment-là.
- **OCR local = défaut recommandé même en ligne** : conformément à la recommandation du prompt, un
  seul pipeline (OCR local) fonctionne identiquement en ligne et hors ligne — pas de dépendance de
  démonstration à une IA non branchée.
- **Limites honnêtes du pipeline d'extraction, non résolues par ce prompt** : `.doc` (binaire Word
  ancien) n'est couvert ni par le texte natif (seul `.docx` l'est) ni par l'OCR (qui a besoin d'une
  image ou d'un PDF rasterisable) — échoue explicitement dans les deux implémentations. Le calque
  texte natif d'un PDF non scanné n'est pas non plus extrait directement : tout PDF, scanné ou non,
  passe par l'OCR local (aucune bibliothèque de lecture de texte PDF n'a été ajoutée, `smalot/
  pdfparser` restant écartée pour raison de licence LGPL depuis `06_PROMPT`).
- **Dépendances système** (`tesseract-ocr` + paquet `fra`, Ghostscript + extension PHP `imagick`) :
  absentes de la machine de développement ayant construit cette itération (Ghostscript présent,
  Tesseract et `ext-imagick` absents, pas d'accès `sudo` sans mot de passe pour les installer).
  `ExtracteurDocumentOcrLocalTest` détecte leur absence (`FriendlyErrors::checkTesseractPresence`)
  et se marque `skipped` avec un message explicite plutôt que d'échouer — le pipeline réel n'a donc
  pas pu être vérifié bout en bout dans cet environnement, seulement son aiguillage et ses garde-fous
  (plafond de confiance, gestion d'erreur). À vérifier réellement sur le poste équipé du jour J.

## 15. `08_PROMPT_ASSISTANT_IA_CONFORMITE` — assistant IA, filtre de sortie, escalade

- **`EscaladesController` sous `Agent\Assistance\`, pas `Admin\Assistance\`** (décision validée
  avec l'utilisateur) : le prompt place cet écran dans le namespace `Admin`, mais
  `responsable_lbcft`/`direction` sont des rôles de l'enum `RoleAgent` (garde `agent`), sans
  équivalent sur le modèle `Admin` (garde `admin`, administrateurs plateforme/réseau). Le tableau
  de bord conformité existant vit déjà sous `Agent\Conformite\`, gardé par le même middleware
  `role.agent:responsable_lbcft,direction` (`routes/agent/conformite.php`) — repris à l'identique
  pour `routes/agent/assistance.php`. Le widget de discussion, lui, reste bien présent dans les
  deux espaces (`Agent\Assistance\AssistantController` et `Admin\Assistance\AssistantController`).
- **Liste des mots interdits extraite en classe partagée** (`App\Support\MotsInterditsConformite`) :
  elle n'existait auparavant que comme constante privée de `NonDivulgationGuichetTest`, malgré
  l'hypothèse du prompt qu'une « policy » partagée existait déjà. Le test a été mis à jour pour
  consommer cette classe. **Recherche par mot entier (`\b`), pas par sous-chaîne** : une recherche
  naïve sur `DOS` aurait fait correspondre chaque occurrence du mot très courant « dossier »
  partout dans l'application, et `PPE` avait déjà fait correspondre « wrapper »/« uppercase »
  (`07_PROMPT` §0) — deux faux positifs réels rencontrés dans ce dépôt qui rendaient la
  correspondance par sous-chaîne inutilisable pour un filtre de production.
- **`GestionnaireAssistant::traiter()` accepte `Agent|Admin`** : le prompt fige la signature sur
  `Agent`, mais l'assistant doit aussi répondre dans l'espace admin (modèle `Admin`, guère
  compatible avec `Agent` — pas de rôle commun, pas de table commune). Un `Admin` n'ayant pas de
  rôle `guichet` possible, `FiltreConformiteReponseIa` ne s'applique qu'aux instances `Agent` avec
  `estGuichet()` — un admin plateforme/réseau n'est jamais soumis à cette restriction (il n'a de
  toute façon jamais accès aux dossiers clients depuis son espace).
- **Base de connaissances alimentée sans réécrire de fichier** : `Services\Assistance\
  BaseConnaissances::charger()` fusionne les fichiers statiques `resources/assistance/*.md` avec
  les lignes `escalades_assistant_ia` déjà `traitee` (`question` + `reponse_responsable`, source
  `reponse_responsable`) — plutôt que d'ajouter au vol un bloc à un fichier Markdown versionné,
  ce qui aurait posé un problème de déploiement (le fichier du dépôt et celui modifié en production
  divergent dès le prochain déploiement). Le résultat fonctionnel demandé par le prompt (« la paire
  est ajoutée à la base de connaissances locale ») est identique.
- **Fournisseur IA externe générique, non mandaté** : ni le « `AiChatController` de référence » ni
  un usage préexistant de `Str::ascii` cités par le prompt n'existent dans ce dépôt (le plus proche
  est `GenerateurEmpreinte::normaliser()`, qui utilise `Str::of(...)->ascii()`, repris à l'identique
  dans `BaseConnaissances`). `ProviderIaApiExterne` cible donc la forme d'API de complétion de chat
  la plus répandue (`{model, messages}`), configurable entièrement par `.env`, sans qu'aucun
  fournisseur précis ne soit choisi ni testé (aucune clé n'est configurée dans ce dépôt).
- **Mode démonstration** : `ASSISTANCE_IA_FORCER_SIMULATEUR=true` force le simulateur même si une
  clé API est configurée et que la connexion fonctionne — recommandé pendant la présentation au
  jury, cohérent avec la prudence déjà documentée pour l'OCR (`07_PROMPT` §4.3) : ne jamais dépendre
  du wifi de la salle.
- **Escalade : question re-filtrée avant enregistrement** : si `MotsInterditsConformite::contient()`
  détecte un mot interdit dans la question elle-même au moment de l'escalade, le texte enregistré
  est remplacé par `'[question filtrée — contenu non enregistré]'` plutôt que la question réelle —
  signalé (l'escalade est tout de même créée, un responsable peut s'apercevoir qu'une tentative de
  contournement a eu lieu), jamais puni ou bloqué pour l'agent.

## 16. Vérification du numéro de téléphone à la saisie — demande orale, sans texte de prompt dédié

Demande formulée hors des prompts écrits (« quand l'agent saisit le numéro de téléphone du client,
retrouver si la personne existe déjà et vérifier le nom réel »), donc appliqué CLAUDE.md §8 : pas de
suppositions, option la plus prudente retenue, documentée ici plutôt que devinée.

- **Écarté d'emblée : toute vérification contre une base téléphonique/opérateur réelle.** Cela
  exigerait soit une donnée personnelle réelle externe, soit d'inventer un format ou un fournisseur
  non fourni — interdit par CLAUDE.md §2.1 et §3. Aucun `Contrats\VerificateurTelephone` de type
  `Simulateur`/`ApiReelle` n'a donc été créé : contrairement au NPI (`VerificateurNpi`), il n'existe
  ici aucune autorité externe à interroger, réelle ou simulable honnêtement.
- **Option retenue : rapprochement intra-réseau, sur les fiches déjà tenues par le SFD.** Exactement
  la fonctionnalité déjà exigée par CLAUDE.md §1 (« consolidation d'un même client multi-agences ») et
  §3 (SI : « recensement des opérations d'un même client », profilage) : si le numéro saisi
  correspond déjà à une fiche du même réseau (`personnes_physiques.telephone_idx`/
  `personnes_morales.telephone_idx`), l'agent est averti au blur, et le nom déclaré est comparé par
  empreinte (`ComparateurEmpreinte::dice`, seuil 0.7, même seuil que `NpiVerificationController`) au
  nom déjà enregistré — un nom sensiblement différent sur un numéro déjà connu est un signal
  d'usurpation ou de doublon, pas un signal de sanction. `App\Services\Kyc\VerificateurTelephoneExistant`,
  `App\Http\Controllers\Agent\Clients\TelephoneVerificationController`, câblé au blur du champ
  téléphone (`window.verifierTelephone`, même patron que `window.verifierNpi`).
- **Portée volontairement limitée au réseau courant** (`ContexteReseau::reseauId()`), jamais
  inter-réseaux : le niveau 2 (inter-réseaux) n'est pas construit dans ce MVP (§1 ci-dessus) et
  l'isolation entre réseaux est un test obligatoire (CLAUDE.md §5 « Qualité »).
- **Le nom trouvé n'est jamais renvoyé au navigateur**, seulement un texte d'avertissement (même
  minimisation que `NpiVerificationController::verifier()`, qui ne renvoie pas non plus
  `nomOfficiel`) : le guichet apprend qu'un doublon probable existe, jamais l'identité complète de
  l'autre fiche depuis ce seul contrôle.
- **Correction d'un oubli découvert en creusant la demande** : `telephone` sur `personnes_physiques`,
  `personnes_morales` et `signataires` était stocké **en clair** (`string` simple), sans passer par
  `App\Casts\ChiffreIndexe` contrairement à `nom`/`email`/`raison_sociale` déjà chiffrés — un écart
  direct à CLAUDE.md §5 (« tout champ d'identité est chiffré »). Corrigé dans la même migration que
  l'ajout de `telephone_idx` (colonne d'index aveugle nécessaire au rapprochement ci-dessus), avec
  rechiffrement des valeurs déjà présentes (migration idempotente, préfixe `v1:` déjà utilisé par
  `App\Services\Securite\Chiffrement` comme garde).
- **Champ câblé au blur pour `personne_physique` et `personne_morale` uniquement** (référentiel
  `config/champs_fiche_adhesion.php`, `type_saisie => 'telephone'`). Le téléphone des **signataires**
  (groupe répétable) reste `type_saisie => 'text'` : il est désormais chiffré comme les autres, mais
  sans contrôle de doublon au blur — le brancher demanderait de dupliquer la logique JS des groupes
  répétables (déjà faite pour le NPI dans `_formulaire.blade.php`) pour un signataire, pas le client
  lui-même, jugé hors du périmètre de la demande initiale (« quand il [le client] veut s'inscrire »).

## 17. `09_PROMPT_TROIS_PROFILS` — fusion des rôles, espace Responsable, comptes admin

- **Fusion `guichet` → `caissier`, `responsable_lbcft` + `direction` → `responsable_agence`** :
  script `php artisan agents:migrer-roles` (`App\Console\Commands\Agents\MigrerRoles`),
  transactionnel, idempotent, ne supprime jamais de ligne (un doublon `responsable_lbcft` +
  `direction` sur la même agence devient deux comptes `responsable_agence` distincts, à nettoyer
  manuellement par un administrateur si besoin — conforme au prompt §2.1). Les anciennes valeurs
  d'enum (`Guichet`, `ResponsableLbcft`, `Direction`) restent dans `App\Enums\RoleAgent`, marquées
  `@deprecated`, en attendant un commit `refactor` séparé une fois la migration de données vérifiée
  en production. **Non exécutée par cette session** ni sur la base de démonstration, à la demande
  explicite du prompt — l'utilisateur relance lui-même `agents:migrer-roles` puis les seeders.
- **Autorisation : middleware `role.agent:...` conservé, pas de nouvelles classes `Policy`.** Le
  prompt demandait des « policies dédiées, une par ressource ». Ce dépôt n'a jamais utilisé de
  `Policy` Laravel pour un contrôle de rôle (seule `ClientPolicy` existe, et c'est un contrôle de
  complétude KYC, pas de rôle) — toute la logique de rôle passe déjà par
  `App\Http\Middleware\RoleAgentAutorise`, générique et journalisant toute tentative refusée
  (`tentative_acces_refusee`). Introduire un système de `Policy` parallèle pour le seul rôle
  n'aurait apporté aucune garantie supplémentaire (même 403 sur accès direct par URL, même
  journalisation) pour un coût de code plus élevé — cohérent avec §6 ci-dessus.
- **`routes/responsable/` est un répertoire multi-fichiers**, pas le fichier unique
  `routes/responsable.php` mentionné littéralement par le prompt — cohérent avec `CLAUDE.md` §5
  (« un fichier par domaine ») et avec `routes/agent/`, `routes/admin/` déjà en place. Chargé par
  un nouveau bloc `glob()` dans `bootstrap/app.php`, guard `agent` partagé avec `routes/agent/*`
  (pas de garde séparée : `caissier` et `responsable_agence` restent deux valeurs de rôle sur la
  même table `agents`, comme demandé par le prompt §6), gate unique
  `role.agent:responsable_agence`.
- **Les clients n'ont pas d'`agence_id` propre** (seulement `reseau_id` — consolidation
  multi-agences volontaire, `CLAUDE.md` §1). Impossible donc de scoper littéralement « les
  dossiers de mon agence » sans une donnée supplémentaire. Option la plus prudente retenue
  (`CLAUDE.md` §8) plutôt que de laisser ces blocs du tableau de bord réseau entier ou de deviner
  un schéma plus lourd : nouvelle colonne `clients.agence_creation_id` (nullable, renseignée à la
  création par `CreateurClient` depuis l'agence de l'agent créateur), combinée avec
  `comptes.agence_id` existant dans un scope Eloquent `Client::scopeDeLAgence()` (« créé ici, ou au
  moins un compte ici »). Utilisé par `Responsable\Tableau\TableauBordController`,
  `Responsable\Filtrage\FiltrageController` et `Responsable\Clients\ClientController`.
- **Vue consolidée d'une identité (`Responsable\Identites\IdentiteController`) : accès conditionné
  à l'agence, contenu non filtré une fois l'accès accordé.** Le prompt interdit explicitement au
  responsable d'agence de voir les autres agences de son réseau (§9, « ce qu'il ne faut surtout pas
  faire »). Mais cet écran existe spécifiquement pour détecter le fractionnement **inter-agences**
  (`CLAUDE.md` §1, §3) : le vider de tout ce qui dépasse l'agence du responsable viderait la
  fonctionnalité de son but. Choix retenu : le responsable ne peut ouvrir la vue consolidée d'une
  identité que si celle-ci a un pied dans son agence (`Client::deLAgence()` sur au moins un des
  clients de l'identité), mais une fois l'accès accordé, la vue reste inchangée (tous les comptes,
  toutes agences confondues) — lecture la plus proche de l'esprit du contrôle réglementaire, la
  restriction du prompt étant interprétée comme « quels dossiers atterrissent dans sa file », pas
  « quelles données peut-il voir sur un dossier qu'il a le droit d'ouvrir ».
- **Fiche RLBC/FT : accès conservé via `Agent\Clients\ClientController` partagé, pas de blocage
  d'écriture pour `responsable_agence`.** Le tableau de permissions du prompt (§3) marque la
  création/complétion client « lecture seule (supervision) » pour le responsable, mais la fiche
  RLBC/FT est une ligne séparée du tableau, marquée simplement « Oui ». Verrouiller tout
  `routes/agent/clients.php` au rôle `caissier` aurait supprimé la capacité déjà testée
  (`FicheRlbcftVisibiliteTest`) du responsable à renseigner cette fiche via le même formulaire
  partagé qu'avant la restructuration. Choix retenu : `routes/agent/clients.php` reste accessible
  aux deux rôles comme avant (aucune régression), et `Responsable\Clients\ClientController` (lecture
  seule, nouveau) s'ajoute pour la supervision de liste — il ne remplace pas l'accès existant.
- **Gestion des comptes côté admin, construite from scratch** (`Admin\Agents\AgentController`,
  `routes/admin/agents.php`, vues `admin/agents/*`) : aucun écran de ce type n'existait avant ce
  prompt (§6 mentionnait déjà l'absence d'écran de gestion, "non construit... faute de temps").
  Périmètre minimal : lister/filtrer par agence et rôle, créer (mot de passe généré affiché une
  seule fois, aucune infra e-mail dans ce dépôt), activer/désactiver, réinitialiser le mot de passe.
  Un admin réseau ne voit et ne peut agir que sur les agences de son propre réseau
  (`estAdminPlateforme()` sinon filtré par `reseau_id`) — plus strict que le précédent
  `Admin\Import\ImportController::creer()`, qui liste toutes les agences sans filtre réseau (écart
  pré-existant, hors périmètre de ce prompt, non corrigé ici).
- **Champ `agents.civilite`** (`m`/`f`/`non_precise`, défaut `non_precise`) : affichage uniquement
  (« Caissier »/« Caissière »/« Caissier / Caissière » via `RoleAgent::libelle(?string $civilite)`)
  — ne change jamais la valeur de code `role`, conforme au prompt §2.2.
- **Personnalisation du logo par réseau (prompt §5.1) : non faite.** Explicitement secondaire dans
  le prompt (« ne pas passer de temps significatif ici »). `layouts/responsable.blade.php` se
  distingue de `layouts/agent.blade.php` par une teinte d'accent différente (bleu plutôt que jaune)
  réutilisant les mêmes tokens CSS, sans logo par réseau ni `reseaux.logo_path`.

## 18. `10_PROMPT_ASSISTANT_IA_DOCUMENTS_ET_INFRA` — outils fixes, bibliothèque, recherche web, MySQL

- **Refus de l'accès SQL libre pour l'IA (§0), remplacé par un jeu fixe d'outils PHP.** Nouveau
  contrat `App\Contracts\OutilAssistantIa` (`nom()`, `description()`, `schemaParametres()`,
  `motsCles()`, `executer()`) — chaque outil est une classe écrite à la main sous
  `app/Services/Assistance/Outils/`, jamais une requête générée par le fournisseur d'IA. Le
  fournisseur ne reçoit jamais `executer()` : `ProviderIaApiExterne` transmet uniquement les
  schémas (function-calling façon OpenAI, `tools`/`tool_choice`) et exécute lui-même l'outil choisi
  côté PHP avant un second appel ; `ProviderIaSimulateur` route par mots-clés
  (`RouteurOutilsMotsCles`) et **formate directement sa réponse depuis le résultat structuré de
  l'outil, sans passer par aucun modèle de langage** — c'est le choix le plus sûr pour la
  non-divulgation, puisque c'est le PHP, jamais un texte généré, qui décide ce qui est montré.
- **Portée agence jamais prise dans les arguments d'un `Agent`** (`Outils\Concerns\ResoutAgenceOutil`) :
  pour un `Agent`, l'agence ciblée est toujours `$utilisateur->agence`, jamais un `agence_id`
  fourni par l'IA — empêche une injection de prompt de faire consulter l'agence d'un autre. Un
  `Admin` peut cibler une agence via argument, mais toujours revalidée contre son `reseau_id`.
- **Règle anti-fraude (§5) : extension de `FiltreConformiteReponseIa`, pas une classe séparée.**
  `masquerSeuils()` recherche, dans toute réponse destinée à un caissier, un groupe de chiffres
  (espace/espace insécable/virgule/point comme séparateur de milliers) qui, une fois nettoyé,
  correspond exactement à une valeur `seuil*`/`montant_min` d'une `regles_detection` active — quelle
  que soit l'origine du texte (outil `consulter_parametre_reglementaire`, base de connaissances,
  fournisseur externe). C'est le seul point de passage garanti pour toute réponse à ce rôle, donc
  le bon endroit pour une garantie qui doit tenir « sous aucune reformulation ».
  `OutilConsulterParametreReglementaire` n'est de toute façon jamais injecté dans le jeu d'outils
  d'un caissier (`OutilsParRole`) — ce filtre est une défense en profondeur, pas la seule barrière.
- **Bibliothèque documentaire — visibilité par lot, pas par fichier individuel.** Le prompt décrit
  « pour chaque fichier déposé, un sélecteur de visibilité ». Option la plus simple retenue
  (`CLAUDE.md` §8) : un seul réglage de portée/visibilité s'applique à tout le lot déposé en une
  fois (jusqu'à 10 fichiers) plutôt qu'un contrôle indépendant par fichier — réduit la complexité du
  formulaire sans contredire l'exigence (l'administrateur choisit la visibilité avant validation).
  Si des visibilités différentes sont nécessaires pour des fichiers différents, l'administrateur
  fait plusieurs upload successifs.
- **`ExtracteurTableurExcel`/`ExtracteurDocumentTexteBrut` implémentent `Contracts\ExtracteurDocument`**
  existant (07_PROMPT) plutôt qu'une nouvelle interface — `SelecteurExtracteurDocument` route
  simplement `.xlsx`/`.xls` et `.txt`/`.md` vers ces nouvelles implémentations, aucune duplication
  du pipeline de stockage/isolation par fichier (`Services\Assistance\TraiteurDocumentIa` réutilise
  le même patron que `Services\Kyc\ExtracteurDocumentClient`).
- **`phpoffice/phpspreadsheet` installé avec `--ignore-platform-req=ext-imagick`** : l'extension
  PHP `imagick` est absente sur cette machine de développement (déjà noté pour l'OCR,
  `docs/DECISIONS.md` §14) ; la lecture `.xlsx`/`.xls` (`IOFactory::load()`) ne l'utilise pas —
  seuls des writers d'image/graphique non utilisés ici en auraient besoin.
- **Trois assistants, trois contrôleurs.** `Responsable\Assistance\AssistantController` (nouveau)
  s'ajoute à `Agent\Assistance\AssistantController` et `Admin\Assistance\AssistantController`
  déjà là. `partials/assistant-ia.blade.php` distingue désormais caissier / responsable_agence /
  admin (plus seulement agent/admin) pour choisir la bonne route — aucune escalade pour responsable
  et admin, ils sont déjà la cible des escalades des caissiers.
- **Recherche web : clés Google absentes, comme prévenu par le prompt.** `GOOGLE_SEARCH_API_KEY`/
  `GOOGLE_SEARCH_ENGINE_ID` ne sont pas fournies à ce dépôt — `SelecteurMoteurRechercheWeb` retombe
  sur `MoteurRechercheWebSimulateur` (résultats fictifs `source = demo`), exactement comme
  `SelecteurProviderIa` le fait déjà pour l'assistant. Aucun connecteur Google Drive ni flux OAuth
  (`GOOGLE_CLIENT_ID`/`SECRET`/`REDIRECT_URI`) n'a été ajouté — explicitement hors périmètre du
  prompt (§4.2, §9) : la bibliothèque documentaire ne s'alimente que par upload direct (§1).
- **Bascule MySQL exécutée, pas seulement documentée.** `.env`/`.env.example` :
  `DB_CONNECTION=mysql`, `DB_HOST=127.0.0.1`, `DB_PORT=3306`, `DB_DATABASE=CIF`, `DB_USERNAME=root`,
  `DB_PASSWORD=` (vide — limite acceptée pour un poste de démonstration local uniquement, jamais en
  production réelle). Base `CIF` créée (`utf8mb4`/`utf8mb4_unicode_ci`) sur le serveur MariaDB local
  déjà en service sur ce poste ; `php artisan migrate` vérifié de bout en bout sur cette base vide.
  **Un incompatibilité réelle trouvée et corrigée** :
  `2026_09_17_001400_create_resultats_filtrage_table.php` avait une contrainte unique
  (`filtrable_type`, `filtrable_id`, `entree_liste_id`) dont le nom auto-généré dépasse la limite
  d'identifiant MySQL (64 caractères) — jamais un problème sous SQLite, qui n'impose pas cette
  limite. Corrigée par un nom explicite et court (`resultats_filtrage_cible_liste_unique`) : la
  contrainte elle-même est inchangée, seul son nom (métadonnée arbitraire) change, donc sans risque
  de dérive pour une base déjà migrée sous SQLite. Les tests restent sur SQLite en mémoire
  (`phpunit.xml` inchangé) — seule la connexion par défaut de l'application change.

## 19. Simulation de dépôt mobile money à l'inscription — demande orale, sans texte de prompt dédié

Demande formulée hors des prompts écrits : reproduire, à l'inscription d'un client, l'écran de
confirmation qu'affiche un vrai transfert mobile money (MTN/Moov/Celtiis Bénin — numéro + nom du
titulaire), pour que le caissier compare visuellement ce nom à celui qu'il saisit. CLAUDE.md §8
appliqué : pas de suppositions, option la plus prudente retenue, documentée ici.

- **Écarté après discussion avec l'utilisateur : un vrai appel USSD ou une vraie API marchand
  MTN/Moov/Celtiis.** Deux raisons, pas seulement réglementaires : (1) techniquement impossible
  depuis une application web sans modem GSM physique ou contrat marchand réel (identifiants,
  sandbox) — aucun des deux n'existe dans ce dépôt ; (2) même réalisable, chaque vérification
  aurait débité un vrai compte et récupéré l'identité d'un vrai abonné, interdit par CLAUDE.md §2.1
  (« aucune donnée personnelle réelle, pas d'extraction d'un système existant »).
- **Option retenue : annuaire numéro → titulaire entièrement synthétique**
  (`comptes_mobile_monnaie_simules`, `source = demo`, seedé par
  `Database\Seeders\Demo\AnnuaireMobileMonnaieSimuleSeeder`, jeu fixe de noms béninois déjà utilisés
  ailleurs dans le dépôt — aucun Faker). `App\Services\Kyc\SimulateurDepotMobileMonnaie` fait une
  recherche exacte par index aveugle (même mécanisme que `personnes_physiques.telephone_idx`),
  jamais un appel réseau. Préfixes opérateurs (`App\Enums\OperateurMobileMonnaie::depuisPrefixe()`)
  codés en dur : c'est le plan de numérotation public béninois, pas une donnée personnelle.
- **Contrairement à `VerificateurTelephoneExistant` (§16), le nom trouvé EST renvoyé au
  navigateur.** Ce n'est pas l'identité d'un autre client interne (donc pas de non-divulgation à
  respecter ici) mais un référentiel externe simulé — exactement ce qu'affiche un vrai écran
  MTN/Moov/Celtiis, que le caissier doit pouvoir lire pour comparer. La comparaison elle-même reste
  faite par empreinte (`ComparateurEmpreinte::dice`, seuil 0.7, même seuil que §16), jamais par
  correspondance exacte de chaîne.
- **Écran récapitulatif ajouté avant la confirmation** (`resources/views/agent/clients/creer.blade.php`,
  étape Alpine `saisie` → `recap`) plutôt qu'un simple ajout d'icône au blur : demande explicite de
  l'utilisateur. Aucune persistance intermédiaire — le récapitulatif est une confirmation UX
  côté navigateur avant le même (et unique) `POST` déjà existant vers `agent.clients.stocker`.
- **Un écart de nom ne bloque jamais le caissier.** « L'outil recommande, l'humain décide »
  (CLAUDE.md §3, dernière ligne) : `App\Services\Kyc\DetecteurIncoherenceDepotSimule`, appelé côté
  serveur dans `CreateurClient::creer()` indépendamment de ce que le caissier a vu à l'écran, se
  contente de lever une `Alerte` (type `IncoherenceDepotSimule`, `faits` = opérateur + score
  uniquement, jamais un nom) visible dans l'espace du responsable de l'agence de création du client.
- **Colonne `agents.email`/`email_idx` ajoutée** (migration additive `add_email_aux_agents`),
  `App\Mail\AlerteConformiteMail` créé sur le même modèle que `App\Mail\RapportJournalierMail`
  (déjà existant pour les rapports quotidiens, §20 ci-dessous — la remarque du §17 « aucune infra
  e-mail dans ce dépôt » était donc erronée, corrigée ici après l'avoir découvert en creusant §20).
  Contenu du mail volontairement minimal (gravité, type, agence, lien vers le tableau de bord) : jamais un nom
  de client, l'e-mail étant un canal moins sûr que l'application. Portée choisie avec
  l'utilisateur : responsables de l'agence de création du client **uniquement** (pas tout le
  réseau) — jamais le caissier connecté (rôle exclu de la requête), jamais un administrateur (table
  `admins` jamais interrogée par ce service). Driver `MAIL_MAILER=log` conservé : l'e-mail est
  réellement construit et mis en file (`Mail::queue`, driver `database`), mais atterrit dans
  `storage/logs/laravel.log` plutôt que sur un vrai SMTP, cohérent avec « terminal standard, sans
  dépendance réseau pour la démo ».

## 20. Alignement du rapport quotidien sur le format papier FECECAM — photos fournies par l'utilisateur

Demande orale, avec photos de gabarits papier réellement utilisés (FECECAM-Bénin, agence Alibori/
Banikoara, tableaux vierges — aucune donnée personnelle sur les photos). CLAUDE.md §8 appliqué.

- **Constat avant toute modification : la fonctionnalité de rapport quotidien existait déjà
  presque intégralement** (`App\Services\Rapports\GenerateurRapportJournalier`,
  `App\Http\Controllers\Agent\Rapports\RapportJournalierController`, `App\Mail\RapportJournalierMail`,
  `App\Console\Commands\Rapports\EnvoyerRapportsQuotidiens` planifiée à 20h dans `routes/console.php`,
  page déjà liée depuis le tableau de bord caissier). Décision : **ne pas reconstruire**, seulement
  combler les écarts trouvés en comparant les photos au PDF déjà produit — cohérent avec « ne pas
  dupliquer un travail déjà fait ».
- **Bug corrigé, hors du périmètre de la demande mais trouvé en creusant** :
  `resources/views/agent/rapports/index.blade.php` chargeait Chart.js depuis
  `cdn.jsdelivr.net` alors que le paquet est déjà installé (`package.json`) et déjà bundlé par Vite
  (`resources/js/app.js` expose `window.Chart`) — le `<script>` CDN était redondant et enfreignait
  CLAUDE.md §2.2 (« pas de CDN ») en plus de risquer de casser la démo hors wifi. Retiré.
- **Écarts comblés** (données déjà calculées ailleurs dans le dépôt, aucune nouvelle notion
  métier) : colonne « Compte » ajoutée sur « Comptes dormants réactivés » (résolue depuis
  `Alerte->faits['compte_id']`, jamais stocké en clair sur l'alerte elle-même) ; colonne « Plafond
  quotidien (cotation) » ajoutée sur « Opérations inhabituelles quotidiennes par cotation »,
  reprenant `Identite::plafond_quotidien_especes` déjà calculé par
  `App\Services\Identite\CalculateurPlafondQuotidien` — le mot « cotation » du gabarit papier
  désigne ce plafond, pas une notion nouvelle.
- **Écarté après question à l'utilisateur : construire une notation de risque client
  (« cotation ») pour la section « Liste des modifications effectuées sur cotations ».** Cette
  notion n'existe nulle part dans le dépôt (aucun champ, aucun modèle, aucun historique) ; l'ajouter
  aurait été une vraie fonctionnalité nouvelle (échelle, règles de changement, autorisation), pas un
  ajustement de rapport. L'utilisateur a choisi de laisser cette section absente pour l'instant —
  à traiter séparément si le besoin est confirmé.
- **Écarté après question à l'utilisateur : reproduire toutes les colonnes KYC du « brouillard des
  ouvertures »** (profession, pièce d'identité, téléphone, adresse, etc., visibles sur les photos).
  L'utilisateur a choisi de garder la version résumée actuelle (4 colonnes) plutôt que ~14.
