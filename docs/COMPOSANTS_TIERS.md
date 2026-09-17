# Composants tiers — CIF-Empreinte

Conformément à `CLAUDE.md` §2 règle 6, ce fichier déclare toute brique tierce et tout
outil d'IA utilisés par le dépôt. Ce fichier n'existait pas avant le prompt
`06_PROMPT_FORMULAIRE_CLIENT_ENRICHI` — c'était un écart de conformité au propre
référentiel du projet, corrigé à cette occasion.

## Outils d'IA utilisés en développement

- **Claude Code** (Anthropic) : assistant de développement utilisé par l'équipe pour
  écrire, revoir et documenter le code de ce dépôt. N'est **pas** un composant
  d'exécution : aucun appel à un modèle Claude n'a lieu en production dans le code livré
  (l'option « IA en ligne » du pipeline d'extraction de documents, §5.2, est prévue mais
  **non implémentée** cette itération — voir `docs/DECISIONS.md`).

## Dépendances Composer / npm

- **Extraction `.docx`** : réalisée avec `ZipArchive`, une extension PHP core (`ext-zip`),
  déjà requise par Laravel lui-même — aucune licence tierce à déclarer.
- **OCR local (`07_PROMPT_MODE_DEGRADE_NPI_OCR` §4)** — implémentation par défaut de
  `Contracts\ExtracteurDocument` pour tout ce qui n'est pas `.docx` (PDF scanné, `.jpg`,
  `.jpeg`, `.png`) :
  - `thiagoalessio/tesseract_ocr` (Composer, **licence MIT**) : pilote le binaire
    `tesseract` depuis PHP.
  - `spatie/pdf-to-image` (Composer, **licence MIT**) : rasterise chaque page d'un PDF en
    image avant OCR.
  - **Dépendances système** (pas de simples paquets Composer — à réinstaller sur tout
    poste vierge, y compris le jour J) :
    - **`tesseract-ocr`** (binaire, licence Apache-2.0) + le paquet de langue
      **`fra`** (français). Windows : installeur officiel
      https://github.com/UB-Mannheim/tesseract/wiki, cocher le composant de langue
      française, puis ajouter le dossier d'installation (ex.
      `C:\Program Files\Tesseract-OCR`) à la variable d'environnement `PATH`.
    - **Ghostscript** (déjà présent sur la machine de développement de l'équipe) +
      **extension PHP `imagick`** — nécessaires uniquement pour rasteriser un PDF (pas
      pour l'OCR direct d'une image `.jpg`/`.png`, qui ne demande que `tesseract`).
      Windows/Laragon/Herd : activer `extension=imagick` dans `php.ini` (DLL PECL
      correspondant à la version de PHP utilisée), installer Ghostscript
      (https://ghostscript.com/releases/gsdnld.html).
  - **Limite assumée** : `.doc` (binaire Word ancien) n'est couvert ni par l'extraction
    texte native ni par l'OCR — aucune des deux bibliothèques ne le lit. Un PDF avec
    calque texte natif (non scanné) passe aussi par l'OCR plutôt qu'une extraction directe
    du calque : aucune bibliothèque de lecture de texte PDF natif (`smalot/pdfparser`,
    LGPL — écartée pour raison de licence dès `06_PROMPT`) n'a été ajoutée.
- **IA en ligne** : `Contracts\ExtracteurDocument` reste prêt pour une future
  implémentation (`config('extraction.preference_en_ligne')`), mais **aucune n'est
  écrite** — l'OCR local reste le seul chemin réel, y compris avec une connexion présente
  (voir `docs/DECISIONS.md`).
- **Vérification NPI** et **connecteur système existant** : les implémentations « réelles »
  (`VerificateurNpiApiReel`, `ConnecteurApiCoreBanking`) utilisent uniquement
  `Illuminate\Support\Facades\Http` (déjà fourni par Laravel), mais ne sont **branchées à
  aucune API réelle** faute d'accès obtenu pour ce hackathon — voir `docs/DECISIONS.md`.
- **Assistant IA conformité (`08_PROMPT_ASSISTANT_IA_CONFORMITE`)** — widget partagé
  agent/admin, `Services\Assistance\ProviderIaApiExterne` : client HTTP générique
  compatible « API de complétion de chat » (forme `{model, messages}`, la plus répandue —
  OpenAI, Groq, OpenRouter et d'autres partagent cette forme). **Aucun fournisseur précis
  n'est mandaté ni testé** dans ce dépôt (aucune clé configurée) : l'équipe choisit le
  fournisseur au moment du déploiement via `.env` (`ASSISTANCE_IA_API_URL`,
  `ASSISTANCE_IA_API_CLE`, `ASSISTANCE_IA_MODELES`). **Aucune dépendance à installer** :
  ni binaire, ni paquet Composer supplémentaire (uniquement `Illuminate\Support\Facades\Http`,
  déjà fourni par Laravel) — contrairement à l'OCR de `07_PROMPT`, cette fonctionnalité
  fonctionne sur un poste vierge dès le déploiement du code, avec pour seul prérequis
  optionnel une clé d'API. En son absence (ou hors connexion, ou si l'appel échoue),
  `Services\Assistance\ProviderIaSimulateur` répond localement par recherche de mots-clés
  dans `resources/assistance/*.md` — c'est le mode recommandé pour la démonstration
  devant le jury (voir `docs/DECISIONS.md`).

## Composants déjà déclarés (hérités du MVP resserré)

Voir `composer.json`/`package.json` : `league/csv`, `barryvdh/laravel-dompdf` (licences
MIT/BSD), `alpinejs`, `chart.js` (MIT). Aucun changement apporté à cette liste par ce
prompt.
