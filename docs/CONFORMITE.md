# Conformité — matrice de permissions par profil

Trois profils (`09_PROMPT_TROIS_PROFILS.md`) : **Caissier** (`agents.role = caissier`, une
agence), **Responsable d'agence** (`agents.role = responsable_agence`, une agence),
**Administrateur** (table `admins`, plateforme ou réseau). Voir `docs/DECISIONS.md` §6 et §17 pour
les décisions d'implémentation associées.

| Écran / donnée | Caissier | Responsable d'agence | Administrateur |
|---|---|---|---|
| Création / complétion client, upload de fiches | Oui | Oui (formulaire partagé, y compris fiche RLBC/FT — voir ci-dessous) + supervision en lecture seule dédiée | Lecture seule |
| Score de complétude KYC, champs manquants | Oui | Oui | Oui |
| Statut PPE, résultats de filtrage, fiche RLBC/FT | **Non, jamais** | Oui | Oui |
| Alertes (fractionnement, seuils, PPE) | **Non, jamais** | Oui (son agence) | Oui (supervision toutes agences) |
| Tableau de bord conformité | **Non, jamais** | Oui (son agence) | Oui (vue consolidée, hors périmètre de ce lot) |
| Déclarations CENTIF | **Non, jamais** | Oui (générer, son agence) | Oui (superviser, hors périmètre de ce lot) |
| Vérification NPI en attente | Voit le statut de son propre dossier saisi | Oui (tous les dossiers de l'agence) | Oui |
| Création/désactivation de comptes caissier et responsable d'agence | **Non** | **Non** | Oui |
| Paramètres réglementaires (seuils, listes de sanctions, référentiel KYC) | **Non** | Lecture seule (pas d'écran dédié dans ce lot) | Oui (modification) |
| Vue consolidée d'une identité (multi-agences, plafond quotidien) | **Non, jamais** | Oui, si l'identité a un pied dans son agence — voir `docs/DECISIONS.md` §17 | Oui |
| Questions escaladées de l'assistant IA | **Non** (pose des questions, ne les traite pas) | Oui | — (espace admin a son propre assistant, sans escalade) |

## Notes d'implémentation

- **Non-divulgation au caissier (Loi art. 63)** : le rôle caissier ne voit jamais les mots
  « soupçon », « DOS », « sanction » ni le nom d'une entrée de liste — filtre serveur
  `App\Support\MotsInterditsConformite`, vérifié par `NonDivulgationCaissierTest`. Son opération se
  déroule normalement à l'écran quel que soit le résultat du filtrage ; l'alerte part directement
  au responsable d'agence.
- **Portée agence du responsable** : les clients n'ont pas d'`agence_id` propre (consolidation
  multi-agences, `CLAUDE.md` §1). Le scope `Client::deLAgence()` combine
  `clients.agence_creation_id` (renseigné à la création) et `comptes.agence_id` (une fois un compte
  ouvert) — voir `docs/DECISIONS.md` §17.
- **Séparation caissier/responsable par URL directe** : toutes les routes `/espace/responsable/*`
  sont protégées par `role.agent:responsable_agence` (`app/Http/Middleware/RoleAgentAutorise.php`),
  qui journalise toute tentative refusée (`tentative_acces_refusee`) — vérifié par
  `SeparationCaissierResponsableTest`.
