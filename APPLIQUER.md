# Lot « identité multi-comptes + plafond quotidien »

Copiez le contenu de ce dossier à la racine du dépôt `vegeta` : les chemins sont déjà
ceux du projet. Six fichiers existants sont **remplacés** (voir plus bas), tout le reste
est nouveau.

```bash
cp -r ./app ./config ./database ./resources ./tests /chemin/vers/vegeta/
cd /chemin/vers/vegeta
php artisan migrate
php artisan db:seed --class="Database\\Seeders\\Detection\\ReglesDetectionSeeder"
php artisan identites:reconstruire     # rattache les fiches déjà en base
php artisan test
vendor/bin/pint
```

## Nouveaux fichiers

| Fichier | Rôle |
|---|---|
| `database/migrations/2026_09_18_0001..0006_*` | `identites`, `clients.identite_id`, `rattachements_identite`, `fusions_identite_en_attente`, `cumuls_journaliers`, `operations.mode_paiement` |
| `config/identite.php` | Seuils de rapprochement et calcul du plafond, avec `source` |
| `app/Enums/ModePaiement.php`, `MethodeRattachement.php`, `StatutFusionIdentite.php` | Énumérations |
| `app/Models/Identite.php`, `RattachementIdentite.php`, `FusionIdentiteEnAttente.php`, `CumulJournalier.php` | Modèles |
| `app/Services/Identite/ResolveurIdentite.php` | NPI d'abord, empreinte en secours, zone grise proposée à l'humain |
| `app/Services/Identite/CalculateurPlafondQuotidien.php` | Plafond déduit du profil déclaré |
| `app/Services/Identite/CompteurCumuls.php` | Agrégat du jour par identité et mode de paiement |
| `app/Services/Detection/SurveillantPlafondQuotidien.php` | Règle `PLAFOND_QUOTIDIEN_IDENTITE` |
| `app/Console/Commands/Identite/ReconstruireIdentites.php` | Rattrapage des fiches existantes |
| `tests/Feature/Identite/*` | 11 tests |

## Fichiers remplacés

| Fichier | Changement |
|---|---|
| `app/Services/Detection/DetecteurFractionnement.php` | Groupe = identité (plus de balayage d'empreintes) ; espèces seulement ; chaque dépôt sous `seuil_unitaire` ; cumul CENTIF sur toutes les fiches |
| `app/Services/Kyc/CreateurClient.php` | Appelle `ResolveurIdentite` après l'écriture du NPI |
| `app/Models/Client.php` | `identite()`, `clientIdsDeLIdentite()` |
| `app/Models/Operation.php` | `mode_paiement` |
| `app/Enums/TypeAlerte.php` | 2 nouveaux types d'alerte |
| `app/Services/Explication/GenerateurExplication.php` | Gabarit plafond ; multi-agences précise le critère de rapprochement |
| `app/Http/Requests/.../CreerOperationRequest.php`, `OperationController.php`, `creer.blade.php` | Champ « mode de paiement » |
| `database/seeders/Detection/ReglesDetectionSeeder.php` | `seuil_unitaire` / `seuil_cumul` + règle plafond |
| `tests/Feature/Detection/DetecteurFractionnementTest.php` | Adapté aux nouveaux seuils et à l'identité |
| `app/Console/Commands/Demo/RejouerScenario.php` | Scénario 3 remis en cohérence : même NPI, 4 dépôts sous le seuil unitaire |

## Ce que ça change pour vos deux cas

- **Base centralisée** : une fiche = une identité, le code ne change pas de chemin.
- **Bases séparées** : N fiches rapprochées par NPI (ou empreinte) = une identité ; tous
  les cumuls (plafond quotidien, fractionnement, seuil CENTIF) passent par elle.

## À faire ensuite (non inclus dans ce lot)

1. Écran « Identité 360° » : comptes, cumul du jour, jauge de plafond, historique des
   rattachements avec leur justification.
2. Écran de la file de fusion pour le responsable LBC/FT (`ResolveurIdentite::fusionner`
   et `ecarterFusion` sont déjà prêts, il manque le contrôleur et la vue).
3. Scénario `php artisan demo:scenario plafond` : trois dépôts, trois agences, alerte.
4. Vérifier que l'écran guichet n'affiche jamais l'alerte de plafond (test de
   non-divulgation à étendre aux deux nouveaux types).

## Points à valider avec les mentors

- Coefficient du plafond (1,5 × revenus mensuels) et plancher : hypothèses de démonstration.
- Seuil unitaire (5 M) et seuil de cumul (15 M) : toujours `source = demo` / `briefing_cif`.
- Seuils de rapprochement par empreinte (0,92 et 0,80) : à confirmer avec l'échantillon
  de `tools/calibration/`.
