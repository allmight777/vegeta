# 00 — Guide d'organisation de l'équipe VEGETA

À lire par les 4 membres **avant** de lancer Claude Code.

---

## 1. Les fichiers et leur ordre

| Ordre | Fichier | Qui le lance | Durée visée | Branche git |
|---|---|---|---|---|
| 0 | `CLAUDE.md` (copié à la racine du dépôt) | Chef d'équipe | 5 min | `main` |
| 1 | `01_PROMPT_SOCLE.md` | Chef d'équipe | J1, 11h00 → 16h00 | `socle` → fusion dans `main` + tag `v0-socle` |
| 2a | `02_PROMPT_NIVEAU_1_INTRA.md` | Binôme niveau 1 | J1 16h00 → J2 17h00 | `n1/intra` |
| 2b | `03_PROMPT_NIVEAU_2_INTER.md` | Binôme niveau 2 | J1 16h00 → J2 17h00 | `n2/inter` |
| 3 | `04_PROMPT_FUSION_DEMO.md` | Chef d'équipe + 1 membre | J2 17h00 → J3 12h00 | `integration` → `main` + tag `v1-final` |

Le socle crée **toutes** les tables, **tous** les modèles, **tous** les contrôleurs (vides), **toutes** les routes et le moteur d'empreinte. Les deux binômes remplissent ensuite des fichiers qui ne se chevauchent pas : la fusion devient quasi mécanique.

## 2. Répartition proposée (à ajuster selon vos forces)

| Binôme | Membres | Pourquoi |
|---|---|---|
| Socle puis niveau 1 | AGOLIGAN Ange (full-stack Laravel) + ATINDEHOU Jean-Eudes (bases de données, modélisation) | Le niveau 1 est le cœur du MVP : schéma, règles, requêtes d'agrégation |
| Niveau 2 | BACHABI Fawaz (réseau, synchronisation, sécurité) + BANDEIRA Abrielle (UX, écrans admin, scénarios de démo, pitch) | Le niveau 2 est fait de frontières : canal CIF, API, nœud hors ligne, écrans de gouvernance |

Rôle de pitch visible (critère « équipe » 5 %) : Abrielle présente le problème et l'impact, Ange la solution, Fawaz manipule la démo hors ligne, Jean-Eudes répond aux questions techniques.

## 3. Préparer le dépôt (chef d'équipe, J1 avant 11h00)

```bash
git clone <URL_ESPACE_GIT_FOURNI> cif-empreinte && cd cif-empreinte
mkdir -p docs/sources docs/prompts
# Copier ici : CLAUDE.md à la racine, les 5 prompts dans docs/prompts/
# Copier dans docs/sources/ : Loi uniforme (PDF), Instruction 001-03-2025 (PDF),
#   briefing règles, guide pitch, charte PI, note CIF-Empreinte, document d'architecture,
#   fiche équipe, photos des fiches d'adhésion, transcription du briefing oral (Notes_*.pdf)
git add . && git commit -m "docs(socle): sources et prompts du projet" && git push
```

**À télécharger dès maintenant sur une connexion fiable et copier dans `docs/sources/`** (je n'ai pas pu en lire le contenu, ils fixent des éléments que le code ne doit pas inventer) :
- Instruction BCEAO n°003-03-2025 du 18 mars 2025 relative à l'identification, la vérification de l'identité et la connaissance de la clientèle (liste des informations KYC et mesures de vigilance renforcées).
- Décision n°021 du 21/12/2023/CM/UMOA fixant les montants des seuils pour la mise en œuvre de la Loi uniforme (seuils réels à mettre dans `parametres` avec `source = reglementaire`).
- Liste consolidée ONU au format XML : `scsanctions.un.org/resources/xml/en/consolidated.xml` → `storage/app/listes/onu_consolidated.xml` (la démo ne doit pas dépendre du wifi).

## 4. Lancer Claude Code

Pour chaque phase, dans le dossier du dépôt :

```text
Lis CLAUDE.md puis docs/prompts/01_PROMPT_SOCLE.md.
Exécute les étapes dans l'ordre. Après chaque étape : pint, tests, commit, résumé court.
Si une information manque, applique la section 8 de CLAUDE.md et continue.
```

Bonnes pratiques :
- Une session Claude Code par étape importante ; `/clear` entre deux étapes pour garder un contexte propre.
- Relire chaque diff de migration et de politique d'accès avant de valider : ce sont les deux zones où une erreur coûte cher.
- Ne jamais demander à Claude Code de « faire tout d'un coup ». Suivre les étapes numérotées.

## 5. Règles git

```bash
# Binôme niveau 1 (même chose pour n2/inter)
git fetch && git checkout -b n1/intra origin/main      # après le tag v0-socle
git commit -m "feat(n1): moteur de règles, cumul journalier espèces"
git fetch origin && git rebase origin/main              # au moins 2 fois par jour
git push -u origin n1/intra
```

- Personne ne pousse directement sur `main` après le tag `v0-socle`, sauf le chef d'équipe pour les demandes de `docs/DEMANDES_SOCLE.md`.
- Quand une demande socle est appliquée sur `main` : les deux binômes font `git rebase origin/main` dans l'heure.
- Points de synchronisation : J1 19h00 (checkpoint jury), J2 12h00, J2 17h00 (début fusion), J3 12h00 (gel interne).

## 6. Pendant que le socle se construit (J1 11h00 → 16h00)

| Binôme niveau 1 | Binôme niveau 2 |
|---|---|
| Écrire `docs/donnees/FRAGMENTS_NOMS.md` : syllabes et variantes orthographiques plausibles (inversions, lettres doublées, voyelles, accents) pour générer des noms synthétiques. Marquer chaque règle comme hypothèse. | Télécharger la liste ONU, l'ouvrir, noter la structure réelle des balises dans `docs/donnees/FORMAT_LISTE_ONU.md`. |
| Rédiger les 6 scénarios de démo en tableau : état initial, action, alerte attendue, texte affiché. | Installer PHP + Composer + SQLite sur un PC Windows cible, vérifier `php -m` (pdo_sqlite, openssl, mbstring, fileinfo). |
| Préparer les questions pour les mentors métier (voir `02_PROMPT_NIVEAU_1_INTRA.md`, section 0). | Préparer les questions pour les mentors techniques (voir `03_PROMPT_NIVEAU_2_INTER.md`, section 0). |
| Préparer le checkpoint J1 19h00 : problème, idée, architecture en 5 min. | Maquettes papier des écrans agent guichet et tableau de bord conformité (mobile d'abord). |

## 7. Ce que le jury doit voir (rappel de la grille)

| Critère | Poids | Preuve à montrer |
|---|---|---|
| Pertinence thématique | 30 % | Filtrage sanctions + PPE à l'entrée en relation et sur chaque opération ; exigences de l'Instr. 001 art. 6 couvertes une par une |
| Originalité | 20 % | Empreintes à préservation de confidentialité, consolidation multi-agences, empreinte des tiers déposants, signal inter-réseaux sans identité |
| Faisabilité technique | 20 % | Démo live hors ligne, Windows + Android, tests verts, taux de faux positifs mesuré et affiché |
| Impact SFD | 15 % | Indicateurs calculés sur les données synthétiques : délai de prise en compte d'une liste, alertes temps réel vs rapport de fin de journée, DTE mensuelle générée |
| Pitch | 10 % | 7 min 30, un cas nommé, un cas qui échoue expliqué, plusieurs voix |
| Équipe | 5 % | Rôles visibles au pitch et dans l'historique git |
