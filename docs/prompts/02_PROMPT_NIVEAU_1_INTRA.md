# 02 — PROMPT NIVEAU 1 : INTRA-RÉSEAU (MVP obligatoire)

> **Non utilisé pour ce build** : voir la note en tête de `01_PROMPT_SOCLE.md`. Conservé pour
> provenance. Le MVP resserré effectivement construit (`05_PROMPT_MVP_RECENTRE.md`) couvre une
> version simplifiée des problèmes 2 et 3 décrits ici (filtrage et fractionnement), avec 4
> règles de détection au lieu des ~23 détaillées plus bas.

## 0. Cadre

Travail sur la branche `n1/intra`, créée depuis le tag `v0-socle`. Zones modifiables limitées
aux zones « Niveau 1 » de la section 6 de `CLAUDE.md`. Consomme `MoteurEmpreinte`,
`EnregistreurClient`, `EnregistreurOperation`, `Parametres`, `JournalAudit`, `IndexAveugle`,
`CoffreDocuments`, `ContexteReseau`. Implémente `MoteurFiltrage`, `MoteurDetection`,
`EvaluateurRisque`, `Reformulateur`. Écoute `ClientEnregistre`, `ClientModifie`,
`PersonneLieeAjoutee`, `OperationEnregistree`, `VersionListeImportee`, `ResultatCanalRecu`.
Déclenche `EmpreinteCalculee`, `NiveauRisqueModifie`, `AlerteCreee`, `AlerteStatutModifie`,
`CorrespondanceSanctionConfirmee`, `MesureGelAppliquee`, `PublicationSignalAutorisee`.

## 1. Priorités

P0 (J2 12h00) : KYC personne physique/morale + personnes liées, import ONU + liste interne,
filtrage entrée en relation + opération, saisie guichet avec contrôle préalable, consolidation
par empreinte, règles P0, alertes temps réel, tableau de bord conformité, gel conservatoire,
non-divulgation guichet, scénario A jouable.

P1 (J2 17h00) : PPE (référentiel, autorisation haute direction, revue 3 ans), évaluation du
risque explicable, règles P1, DOS avec double validation, DTE mensuelle, examen particulier,
revues périodiques, calibration, indicateurs, scénarios C/D/F/G.

P2 (J3 11h00 si le temps le permet) : règles P2, export rapport annuel, vérification téléphone
OTP, scan QR, reformulation LLM (désactivée par défaut).

## 2 à 15 — Contenu détaillé

KYC et entrée en relation (formulaires calqués sur les fiches CIF, personnes liées empreintées
et filtrées, contrôle du bénéficiaire effectif > 25 %, machine d'états client, visa RLBC/FT) ;
listes de sanctions (import ONU en flux XMLReader, versionnement/diff, refiltrage « sans délai »
avec SLA 24h, import CSV générique) ; référentiel PPE ; filtrage (MoteurFiltrage, écran de
décision conformité, conséquences gel conservatoire/confirmation/faux positif) ; évaluation du
risque (facteurs pondérés en classes dédiées, planchers PPE/sanction, contributions tracées) ;
consolidation d'un même client (rapprochements, groupes d'identité par union-find, solde
consolidé) ; opérations au guichet (saisie 3 écrans, `MoteurDetection::controlerAvantExecution`,
message neutre) ; moteur de détection (~23 règles P0/P1/P2 avec déduplication par fenêtre) ;
workflows de conformité (alertes, examen particulier, DOS, DTE, gel, autorisations, revues) ;
non-divulgation au guichet (art. 63, tests bloquants) ; explications en gabarits déterministes ;
données synthétiques et calibration ; tableaux de bord et indicateurs ; écrans d'administration.

Détail complet (signatures, seeders, scénarios A/C/D/F/G, définition de « terminé ») transmis à
l'équipe séparément — non reproduit intégralement ici, l'équipe ayant basculé sur le MVP
resserré avant construction. Voir `docs/DECISIONS.md` pour la correspondance avec ce qui a
effectivement été construit (4 règles de détection, filtrage simplifié sans MinHash branché en
production, pas de workflow DOS complet).
