# ARCHITECTURE_ACTUELLE.md — Audit d'état réel du dépôt (Étape 0)

> Produit conformément à `docs/prompts/12_PROMPT_IA_INTEGREE_PROFONDE.md` §1, **avant toute
> implémentation** de l'IA intégrée profonde. Basé sur une lecture réelle du code (migrations,
> modèles, services, routes, vues, contrôleurs) au 2026-09-18 — pas sur les prompts précédents ni
> sur des suppositions. Ce document est un livrable à valider avant de coder quoi que ce soit.
>
> Rappel du contexte (`CLAUDE.md`) : le dépôt a été construit avec le MVP resserré
> (`05_PROMPT_MVP_RECENTRE.md`), pas avec le découpage socle/niveau 1/niveau 2. Une seule base
> (SQLite en dev/tests), un seul niveau d'accès aux données.

---

## 1. Tables existantes

39 fichiers de migration → 38 tables (6 tables techniques Laravel + 32 tables applicatives, une par
modèle Eloquent). **Aucun trait `AppartientAuReseau` n'existe dans le code** (`grep -r
AppartientAuReseau app/` ne retourne rien) : contrairement à ce que laisse penser `CLAUDE.md` §5,
le scoping réseau/agence est assuré à 100% manuellement, par colonnes FK directes ou par chaînes de
relations/scopes Eloquent ad hoc (ex. `Client::scopeDeLAgence()`), sans mécanisme transverse
centralisé.

| Table | Modèle | Colonnes clés | Données d'identité chiffrées | Scope | Notes |
|---|---|---|---|---|---|
| sessions, cache, cache_locks, jobs, job_batches, failed_jobs | — | tables Laravel par défaut | — | technique | `sessions.user_id` générique, partagé admins+agents |
| reseaux | Reseau | `id` auto, `nom`, `code` unique | aucune | global (racine du scope réseau) | table de référence |
| agences | Agence | `id` auto, `reseau_id` FK, `nom`, `code` (unique avec reseau_id) | aucune | réseau (`reseau_id`) | — |
| admins | Admin | `id` auto, `reseau_id` FK nullable, `email`/`email_idx` | `email` (ChiffreIndexe) | réseau (nullable = admin plateforme) | `estAdminPlateforme()` = `reseau_id === null` |
| agents | Agent | `id` auto, `agence_id` FK, `matricule`/`matricule_idx`, `role`, `civilite`, `email`/`email_idx` | `matricule`, `email` (ChiffreIndexe) | agence (→ réseau indirect) | `civilite`/`email` ajoutés après coup |
| clients | Client | `id` uuid, `reseau_id` FK, `agence_creation_id` FK nullable, `identite_id` uuid nullable (sans FK), `type`, `nature_relation`, `statut_ppe`, `statut_verification_npi`, `source_creation`, `score_completude_kyc`, softDeletes | aucune directement | réseau (`reseau_id`) | `scopeDeLAgence()` combine `agence_creation_id` OU `comptes.agence_id` |
| personnes_physiques | PersonnePhysique | `id` uuid, `client_id` FK unique, `nom`/`nom_idx`, `npi_idx` (index seul, pas de npi en clair), `revenus_mensuels_estimes` decimal, `empreinte_nom`/`empreinte_combinee` binary, `champs_manquants` json, softDeletes | `nom`(ChiffreIndexe), `prenoms`/`date_naissance`/`lieu_naissance`/`piece_identite_numero`/`adresse`/`domicile`/`pere`/`mere`/`conjoint` (Chiffre), `telephone`/`email` (ChiffreIndexe) | via `client_id`→reseau | Très nombreuses colonnes fiche d'adhésion ajoutées après coup ; `telephone_idx` ajouté par migration de rattrapage |
| personnes_morales | PersonneMorale | `id` uuid, `client_id` FK unique, `raison_sociale`/`raison_sociale_idx`, `rccm_idx`/`ifu_idx`, `empreinte_nom` binary, `champs_manquants` json, softDeletes | `raison_sociale`, `rccm`, `ifu`, `telephone`, `email` (ChiffreIndexe), `adresse` (Chiffre) | via `client_id`→reseau | `beneficiaire_effectif_texte` en clair (texte libre, voir §6) |
| signataires | Signataire | `id` uuid, `personne_morale_id` FK, `nom`/`nom_idx`, `role`, `pourcentage_detention`, `statut_filtrage`, `npi_idx` (index seul), softDeletes | `nom` (ChiffreIndexe), `date_naissance`/`lieu_naissance`/`piece_identite_numero`/`adresse` (Chiffre), `telephone` (ChiffreIndexe) | via personne_morale→client→reseau | `fonction` en clair (texte libre) |
| import_lots | ImportLot | `id` uuid, `agence_id` FK, `nom_fichier`, compteurs, `statut` | aucune | agence | — |
| import_lignes | ImportLigne | `id` uuid, `import_lot_id` FK, `client_id` FK nullable, `donnees_brutes`/`champs_manquants` json | aucune | via import_lot→agence, client→reseau | **`donnees_brutes` json peut contenir de l'identité en clair issue du CSV importé — non chiffré** |
| comptes | Compte | `id` uuid, `client_id` FK, `agence_id` FK, `numero`/`numero_idx` unique | `numero` (ChiffreIndexe) | agence + réseau (via client) | — |
| operations | Operation | `id` uuid, `compte_id` FK, `agence_id` FK, `agent_id` FK nullable, `montant` decimal, `mode_paiement`, `effectuee_le` | aucune | agence | Suppression bloquée (conservation légale) |
| entrees_liste | EntreeListe | `id` auto, `source`, `nom`/`nom_idx`, `empreinte_nom` binary, `prenom`/`npi`/`pays`/`telephone` | `nom` (ChiffreIndexe) — **`npi`, `telephone`, `prenom`, `pays` stockés en clair, sans cast, contrairement à la convention "index aveugle seul" appliquée ailleurs au NPI** | global (référentiel de listes, pas de reseau_id) | Incohérence à noter |
| resultats_filtrage | ResultatFiltrage | `id` uuid, `filtrable_type`+`filtrable_id` (polymorphe client/signataire), `entree_liste_id` FK, `score_similarite`, `statut`, `motif_ecart` | `motif_ecart` (Chiffre) | indirect (via cible polymorphe → reseau) | — |
| regles_detection | RegleDetection | `id` auto, `code` unique, `parametres` json, `source`, `reference_texte` | aucune | global | **Table de configuration** — `source` = enum `SourceValeur` |
| alertes | Alerte | `id` uuid, `client_id` FK, `agence_id` FK nullable, `regle_detection_id`/`resultat_filtrage_id` FK nullables, `faits` json, `gravite`, `statut`, softDeletes | aucune (`faits` = jamais de nom en clair, uniquement faits techniques) | client (réseau) + `agence_id` nullable ajouté après coup | — |
| declarations_centif | DeclarationCentif | `id` uuid, `client_id` FK, `montant_cumule`, `periode`, `fichier_pdf_path`, softDeletes | aucune | client (réseau) | unique `(client_id, periode)` |
| journal_audit | JournalAudit | `id` auto, `acteur_type`+`acteur_id`, `cible_type`+`cible_id` (sans FK), `hash_precedent`/`hash_courant` char(64) | aucune | global | append-only, chaînage type blockchain |
| mandataires | Mandataire | `id` uuid, `personne_physique_id` FK, `nom`/`nom_idx`, softDeletes | `nom` (ChiffreIndexe), `prenoms` (Chiffre) | via personne_physique→client→reseau | `lien_parente` en clair (texte libre) |
| fiches_rlbcft | FicheRlbcft | `id` uuid, `controlable_type`+`controlable_id` (polymorphe), booléens PPE/sanction/financement, `visa_rlbcft_*`, softDeletes | aucune | indirect | invisible au caissier (contrôlé par policy, pas par le schéma) |
| documents_clients | DocumentClient | `id` uuid, `import_lot_id` uuid (sans FK, domaine distinct de import_lots malgré le nom), `client_id` FK nullable, `chemin_fichier`, `donnees_extraites` json nullable, `traite_par_agent_id` FK, softDeletes | aucune (json non chiffré, mais jamais affiché avant validation selon le modèle) | via client (nullable tant que non rapproché) | — |
| verifications_npi_en_attente | VerificationNpiEnAttente | `id` uuid, `client_id` FK, `signataire_id` FK, `npi_idx`, `npi_chiffre` nullable, `statut`, `tentatives` | `npi_chiffre` (Chiffre) | via client/signataire→reseau | **Seule colonne NPI chiffrée récupérable du schéma** — exception documentée, nécessaire pour resoumettre la vérification |
| escalades_assistant_ia | EscaladeAssistantIa | `id` uuid, `agent_id` FK, `question`/`reponse_ia`/`reponse_responsable`, `contexte_ecran`, `statut`, `traitee_par_agent_id` FK | aucune | agence (via agent) | — |
| identites | Identite | `id` uuid, `reseau_id` FK, `npi_idx` nullable, `empreinte_combinee` binary, `plafond_quotidien_especes`, `source_plafond`, `base_calcul_plafond`, softDeletes | aucune donnée nominative en clair | réseau | unique `(reseau_id, npi_idx)` |
| comptes_mobile_monnaie_simules | CompteMobileMonnaieSimule | `id` auto, `telephone`/`telephone_idx` unique, `operateur`, `nom_titulaire`, `source` (défaut demo) | `telephone`, `nom_titulaire` (Chiffre) | global | **Annuaire synthétique de démo**, entièrement fictif |
| rattachements_identite | RattachementIdentite | `id` uuid, `identite_id` FK, `client_id` FK, `methode`, `score`, `decide_par_agent_id` FK, `motif` | aucune | via identite→reseau | traçabilité du rapprochement |
| fusions_identite_en_attente | FusionIdentiteEnAttente | `id` uuid, `identite_source_id`/`identite_cible_id` FK, `score`, `statut`, `decide_par_agent_id` FK | aucune | via identités→reseau | décision humaine obligatoire |
| cumuls_journaliers | CumulJournalier | `id` uuid, `identite_id` FK, `jour`, `mode_paiement`, `total_depots`/`total_retraits`, `nb_operations`/`nb_comptes`/`nb_agences` | aucune | via identite→reseau | unique `(identite_id, jour, mode_paiement)` |
| documents_ia | DocumentIa | `id` uuid, `titre`, `chemin_fichier`, `contenu_extrait` text nullable, `visible_caissier`/`visible_responsable_agence`/`visible_administrateur` bool, `reseau_id`/`agence_id` FK nullables, `televerse_par_admin_id` FK, softDeletes | aucune | réseau/agence **nullables** (NULL = document global) | alimenté uniquement par upload admin |
| decisions_filtrage | DecisionFiltrage | `id` uuid, `cle_decision` unique, `identite_id` nullable (sans FK), `portee`, `source_liste`, `statut`, `motif_code`, `motif_detail`, `decide_le`/`expire_le`, `applications` | `motif_detail` (Chiffre) | via identite quand renseigné | `cle_decision` = empreinte du fait (pas un ID de ligne) — la décision suit la personne |
| rapports_journaliers | RapportJournalier | `id` uuid, `agence_id` FK, `genere_par_agent_id` FK, `date_debut`/`date_fin`, `fichier_pdf_path`, `destinataire_email`, `jeton_partage` unique, `code_acces_hash` | `destinataire_email` (Chiffre, sans index associé) | agence | — |
| users_password | UserPassword | `id` auto, `agent_id` FK, `mot_de_passe_chiffre` | aucune (mot de passe, hors périmètre identité) | via agent→agence | historisé (plusieurs lignes possibles) |

**Trois enums "source" distincts à ne pas confondre** : `SourceValeur` (reglementaire /
briefing_cif / politique_interne / demo — utilisé par `regles_detection.source`,
`identites.source_plafond`), `SourceListeType` (onu / ppe_benin / ppe_cedeao / demo — utilisé par
`entrees_liste.source`), `SourceCreation` (saisie_agent / import_csv / import_document — utilisé
par `clients.source_creation`).

---

## 2. Services (`app/Services/`)

65 fichiers PHP, 15 sous-dossiers, tous couverts ci-dessous. Seul service confirmé **non branché en
production** : `Empreinte/IndexBlocageMinHash.php` (aucun appelant hors test unitaire, son propre
docblock le confirme).

### Assistance/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| BaseConnaissances | Charge la base de connaissances (`resources/assistance/*.md` + escalades traitées) | ProviderIaSimulateur, OutilBaseConnaissancesProduit, ConstructeurContexteIa | `Collection` / `?array` |
| ConstructeurContexteIa | Construit le contexte fermé envoyé à l'IA (jamais d'identité) | ProviderIaApiExterne, GestionnaireAssistant, AssistantController (Agent) | `array` |
| ExtracteurDocumentTexteBrut | Extrait le contenu d'un `.txt`/`.md` | SelecteurExtracteurDocument | `ResultatExtraction` |
| ExtracteurTableurExcel | Convertit un `.xlsx`/`.xls` en texte tabulaire | SelecteurExtracteurDocument | `ResultatExtraction` |
| FiltreConformiteReponseIa | Filtre de sortie serveur qui masque les données sensibles (ex. seuils exacts) selon le rôle | GestionnaireAssistant | `string` |
| GestionnaireAssistant | Orchestrateur unique : contexte → provider (avec repli) → filtre → journalisation | AssistantController (Admin/Agent/Responsable) | `array` |
| MoteurRechercheWebApiExterne | Interroge l'API Google Custom Search | SelecteurMoteurRechercheWeb | `array` |
| MoteurRechercheWebSimulateur | Résultats web fictifs (`source=demo`) hors clés/hors connexion | SelecteurMoteurRechercheWeb | `array` |
| ResoutAgenceOutil (trait) | Résout l'agence ciblée par un outil IA (toujours celle de l'agent, jamais un argument fourni par l'IA) | OutilStatistiquesAgregeesAgence, OutilCompterProfilsIncomplets, OutilCompterAlertesDuJour | n/a |
| OutilBaseConnaissancesProduit | Outil IA : lexique produit / guides d'écran | OutilsParRole | `array` |
| OutilCompterAlertesDuJour | Outil IA : compte les alertes ouvertes du jour par gravité, réservé responsable/admin | OutilsParRole | `array` |
| OutilCompterProfilsIncomplets | Outil IA : compte/liste les dossiers KYC incomplets | OutilsParRole | `array` |
| OutilConsulterParametreReglementaire | Outil IA : retourne un seuil avec sa source, réservé responsable/admin | OutilsParRole | `array` |
| OutilRechercheDocumentaire | Outil IA : recherche mots-clés dans `documents_ia.contenu_extrait` | OutilsParRole | `array` |
| OutilRechercherClientExistant | Outil IA : confirme seulement l'existence d'un dossier externe, jamais son contenu | OutilsParRole | `array` |
| OutilRechercheWeb | Outil IA : recherche web publique, retourne des liens à paraphraser | OutilsParRole | `array` |
| OutilStatistiquesAgregeesAgence | Outil IA : comptages agrégés non-identifiants, réservé responsable/admin | OutilsParRole | `array` |
| OutilsParRole | Construit le jeu d'outils IA disponibles selon le rôle | GestionnaireAssistant, AssistantController ×3 | `array` |
| ProviderIaApiExterne | Client générique compatible chat (format {model, messages}) avec function-calling, une itération | SelecteurProviderIa, GestionnaireAssistant | `string` |
| ProviderIaIndisponibleException | Levée quand tous les modèles de repli ont échoué | ProviderIaApiExterne → catch GestionnaireAssistant | n/a |
| ProviderIaSimulateur | Fournisseur IA simulé par mots-clés (routage outil puis base de connaissances) | RouteurOutilsMotsCles, GestionnaireAssistant, SelecteurProviderIa | `string` |
| RouteurOutilsMotsCles | Associe une question à l'outil le plus pertinent par recoupement de mots-clés, exécute et formate | ProviderIaSimulateur | `?string` |
| SelecteurMoteurRechercheWeb | Bascule réel/simulateur (démo forcée > clés absentes > hors connexion > réel) | OutilRechercheWeb | `MoteurRechercheWeb` |
| SelecteurProviderIa | Bascule fournisseur réel/simulateur (mêmes règles) | SelecteurMoteurRechercheWeb, GestionnaireAssistant | `ProviderIa` |
| TraiteurDocumentIa | Traite un document de la bibliothèque IA : stockage → extraction → `contenu_extrait` | DocumentIaController (Admin) | `DocumentIa` |

### Audit/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| Consignateur | Écrit une entrée de journal d'audit chaînée par hachage et vérifie son intégrité | Très largement utilisé (quasi tous les contrôleurs + plusieurs services) | `JournalAudit` / `?int` |

### Contexte/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| ContexteReseau | Résout le réseau de l'utilisateur courant | Plusieurs contrôleurs Agent (Clients, Operations, Tableau, Assistance), Responsable/Identites | `?int` |

### Detection/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| DetecteurFractionnement | Règles de détection de fractionnement (personne = unité de contrôle, espèces uniquement) après chaque opération | OperationController (Agent), RejouerScenario | `void` |
| SurveillantPlafondQuotidien | Vérifie le cumul espèces du jour d'une personne vs plafond déduit du profil | DetecteurFractionnement | `void` |

### Empreinte/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| ComparateurEmpreinte | Score de similarité Dice entre deux vecteurs (+ score composite nom+date) | VerificateurTelephoneExistant, DetecteurIncoherenceDepotSimule, ServiceEmpreinte, NpiVerificationController | `float` |
| GenerateurEmpreinte | Génère l'empreinte (normalisation → bigrammes → HMAC-SHA256 → filtre de Bloom) | ImportateurCsv, ConnecteurImportLocal, DetecteurIncoherenceDepotSimule, VerificateurTelephoneExistant, ServiceEmpreinte, NpiVerificationController | `Bitset` / `string` |
| IndexBlocageMinHash | Blocage MinHash pour import de masse | **Aucun appelant en production** (confirmé par le docblock ; seulement testé unitairement) | `array` |
| ServiceEmpreinte | Façade du domaine empreinte, maintient `empreinte_nom`/`empreinte_combinee` sur les modèles | ImportateurCsv, ConnecteurImportLocal, MoteurFiltrage, ResolveurIdentite, ImportController (Admin) | `void` / `?float` |

### Explication/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| GenerateurExplication | Phrases-gabarits déterministes expliquant chaque type d'alerte | DetecteurIncoherenceDepotSimule, MoteurFiltrage, SurveillantPlafondQuotidien, DetecteurFractionnement | `string` |

### Filtrage/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| AlertesPpeTempsReel | Mail immédiat au responsable si correspondance forte (≥85%) PPE/sanctions | CreateurClient, ClientController (Agent) | `void` |
| GenerateurRecapPpeJour | Récapitulatif quotidien mail des correspondances PPE/sanctions, par agence | EnvoyerRecapsPpeQuotidiens (commande) | `int` / `bool` |
| MemoireDecisions | Mémorise les décisions de filtrage déjà tranchées pour éviter de relever une alerte déjà écartée | MoteurFiltrage, FiltrageController (Responsable) | `?DecisionFiltrage` / `void` |
| MoteurFiltrage | Compare l'empreinte d'une cible aux entrées de liste et génère une alerte si seuil dépassé, en consultant MemoireDecisions | ImportateurCsv, CreateurClient, AlertesPpeTempsReel, PublierSignal, RejouerScenario, ImportController (Admin), SignataireController, ClientController (Agent) | `Collection` |

### Identite/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| CalculateurPlafondQuotidien | Déduit le plafond quotidien d'espèces du profil KYC (Instr. BCEAO 001-03-2025 art. 6) | ResolveurIdentite | `array` / `void` |
| CompteurCumuls | Entretient le cumul du jour par identité/mode de paiement à chaque opération | DetecteurFractionnement | `?CumulJournalier` |
| ResolveurIdentite | Rattache une fiche client à la personne réelle (NPI prioritaire, empreinte en secours, fusion arbitrée par le responsable) | CreateurClient, ReconstruireIdentites, RejouerScenario | `?Identite` / `Identite` / `void` |

### Import/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| ImportateurCsv | Importe un CSV core banking avec aperçu puis rapprochement par empreinte + date de naissance | ConnecteurImportLocal, ImportController (Admin) | `array` / `ImportLot` |

### Kyc/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| CalculateurCompletude | Compare les champs renseignés au référentiel et calcule le taux de complétude | ClientPolicy, CreateurClient, ImportateurCsv, RejouerScenario, ImportController, SystemeExistantController, ClientController | `array` |
| ConnecteurApiCoreBanking | Implémentation API réelle (écrite, **jamais branchée par défaut**) | binding conditionnel AppServiceProvider | `?array` |
| ConnecteurImportLocal | Connecteur système existant par défaut : recherche par NPI (index aveugle) ou empreinte nom+date | binding par défaut, via interface `ConnecteurSystemeExistant` | `?array` |
| CreateurClient | Factorise la création complète d'un client (personne physique/morale, mandataires/signataires, fiche RLBC/FT) | ImportDocumentController, ClientController (Agent) | `Client` |
| DetecteurIncoherenceDepotSimule | Contrôle non bloquant, alerte le seul responsable en cas d'incohérence dépôt mobile money / identité déclarée | CreateurClient | `void` |
| ExtracteurDocumentClient | Orchestre le pipeline document client : stockage chiffré → extraction → mapping → enregistrement | ImportDocumentController | `DocumentClient` |
| ExtracteurDocumentOcrLocal | OCR local (Tesseract/Imagick), hors connexion | SelecteurExtracteurDocument | `ResultatExtraction` |
| ExtracteurDocumentTexteNatif | Extraction texte natif `.docx` via ZipArchive (sans dépendance Composer) | SelecteurExtracteurDocument | `ResultatExtraction` |
| MappeurChampsExtraits | Fait correspondre le texte extrait au référentiel de la fiche d'adhésion | ExtracteurDocumentClient, ExtracteurDocumentOcrLocal | `array` / `string` |
| ReferentielFicheAdhesion | Point d'accès unique à `config/champs_fiche_adhesion.php` | MappeurChampsExtraits, CalculateurCompletude, ConstructeurContexteIa, FormRequests Clients, SystemeExistantController | `array` / `?array` |
| ResolveurStatutNpi | Point de décision unique du statut NPI (en ligne bloquant / hors ligne en attente) | CreateurClient, NpiValideRegle, ClientController, SignataireController | `array` |
| SelecteurExtracteurDocument | Aiguille vers la bonne implémentation d'extraction selon le type MIME | ExtracteurDocumentClient, TraiteurDocumentIa | `ExtracteurDocument` |
| SimulateurDepotMobileMonnaie | Simule un transfert mobile money via annuaire synthétique et index aveugle | DetecteurIncoherenceDepotSimule, SimulationDepotMobileMonnaieController | `ResultatSimulationDepot` |
| ValidateurFormatNpi | Contrôle local du format NPI (10 chiffres, hypothèse démo) | VerificateurNpiSimulateur, ResolveurStatutNpi | `bool` |
| VerificateurNpiApiReel | Appel API RAVIP réel (écrit, **jamais branché par défaut**) | binding conditionnel | `ResultatVerificationNpi` |
| VerificateurNpiSimulateur | Simulateur NPI de démo (valide tout NPI plausible sauf 2 NPI de test) | binding par défaut, via interface `VerificateurNpi` | `ResultatVerificationNpi` |
| VerificateurTelephoneExistant | Recherche un doublon par téléphone (index aveugle exact) dans le réseau de l'agent | DetecteurIncoherenceDepotSimule, TelephoneVerificationController | `ResultatVerificationTelephone` |

### Listes/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| ImportateurListes | Importe un fichier Excel/CSV d'entrées de liste sanctions/PPE | EntreeListeController (Admin) | `int` |
| NoopImport | Classe marqueur vide (contrat `Maatwebsite\Excel\Concerns\Import`) | ImportateurListes | n/a |

### Operations/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| DetecteurPlafondInterAgences | Après chaque dépôt espèces, vérifie le cumul inter-agences et alerte la chaîne conformité (jamais le caissier) | OperationController (Agent) | `void` |

### Rapports/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| GenerateurDeclarationCentif | Génère le PDF de démonstration d'une déclaration CENTIF | DeclarationCentifController | `string` |
| GenerateurRapportJournalier | Génère le rapport journalier d'activité d'une agence | EnvoyerRapportsQuotidiens (commande), RapportJournalierController | `RapportJournalier` / `array` |

### Reseau/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| DetecteurConnectiviteHttp | Teste la connectivité internet (requête HTTP légère, cache driver database) | binding interface `DetecteurConnectivite`, consommé par ResolveurStatutNpi, SelecteurMoteurRechercheWeb, SelecteurProviderIa | `bool` |

### Securite/
| Classe | Rôle | Appelé par | Retourne |
|---|---|---|---|
| Chiffrement | Chiffre/déchiffre une valeur avec clé dérivée par usage | Casts ChiffreIndexe, Chiffre | `string` / `?string` |
| GestionnaireCles | Sous-clé HMAC dérivée par usage depuis `CLE_CIF_DEMO` (jamais `APP_KEY`) | Chiffrement, IndexAveugle, GenerateurEmpreinte, IndexBlocageMinHash | `string` |
| IndexAveugle | Calcule un index HMAC de recherche exacte sur une valeur sensible | Très largement utilisé (Kyc, Filtrage, Models, commandes, contrôleurs Auth/Agents/Clients) | `string` |

Deux classes Kyc écrites mais jamais sélectionnées par défaut (config `simulateur`/`local`) :
`ConnecteurApiCoreBanking`, `VerificateurNpiApiReel` — comportement volontaire, documenté dans
leurs docblocks.

---

## 3. Écrans (routes + vues, trois espaces)

Montage (`bootstrap/app.php`) : `routes/admin/*` → guard `admin`, préfixe URL `admin`, préfixe nom
`admin.` · `routes/agent/*` → guard `agent`, préfixe URL `espace`, préfixe nom `agent.` ·
`routes/responsable/*` → guard `agent` + middleware `role.agent:responsable_agence`, préfixe URL
`espace/responsable`, préfixe nom `responsable.`. Les routes de connexion sont dans `routes/web.php`
(hors des trois dossiers ci-dessus) ; il n'existe pas de dossier `responsable/authentification` — le
responsable se connecte via la même route `agent.connexion.*`.

**Formulaires clients pilotés par schéma de configuration** : la majorité des champs de
`agent/clients/creer.blade.php` et `completer.blade.php` n'ont pas de `name=` statiques — ils sont
générés dynamiquement depuis `config/champs_fiche_adhesion.php` via les partials `_formulaire.blade.php`
/ `_champ.blade.php`. C'est la source de vérité du référentiel KYC (voir aussi §6).

### Espace Caissier / Agent (`/espace`)

**Clients** : `agent.clients.index` (liste + recherche `q`), `.creer`/`.stocker` (formulaire complet
piloté par le référentiel : identification, coordonnées, filiation, activité — `profession`,
`activite_1/2` —, versements, mandataires/signataires répétables, signatures), `.lookup`/`.npi.verifier`
/`.telephone.verifier`/`.simulation-depot.verifier` (JSON, pas de vue), `.completer`/`.mettre-a-jour`
(même formulaire + groupe `fiche_rlbcft` visible seulement si responsable), `.systeme-existant.*`
(pré-remplissage depuis connecteur externe), `.signataires.*`/`.mandataires.*` (CRUD inline),
`.import.*` (upload documents → OCR → revue → validation, mêmes champs dynamiques préremplis).

**Comptes** : `agent.comptes.stocker` (formulaire d'ouverture inline dans `completer.blade.php`).

**Opérations** : `agent.operations.historique` (filtres client/compte/type/montant/dates),
`.creer`/`.stocker` (`compte_id`, `type`, `montant`, `mode_paiement`).

**Rapports** : `agent.rapports.index`/`.generer` (`date_debut`/`date_fin`), `.telecharger`,
`.envoyer` (`email`, `code_acces`, `code_acces_confirmation`).

**Tableau de bord** : `agent.tableau-de-bord.index` (recherche `q`).

**Assistance** : `agent.assistant.repondre`/`.escalader` (JSON, widget intégré, pas de vue dédiée).

### Espace Responsable (`/espace/responsable`)

**Clients** (lecture seule) : `.index` (liste + recherche), `.afficher` (consultation pure, aucun
formulaire).

**Identités** : `.afficher` (consultation pure).

**Filtrage** : `.index` (liste + filtre statut), `.decider` (`motif`, `motif_code` — écarter/confirmer
une correspondance).

**Conformité** : `.declarations-centif.generer`, `.alertes-npi.traiter` (actions sans vue dédiée,
déclenchées depuis le tableau de bord).

**Assistance** : `.assistant.repondre` (JSON), `.assistance.escalades.index`/`.repondre`
(`reponse_responsable`).

**Tableau de bord** : `.tableau-de-bord.index` (recherche `q`).

### Espace Admin (`/admin`)

**Agents** : `.index` (recherche `q`, filtres `role`/`agence_id`, + formulaire envoi PDF `email`/
`mot_de_passe_pdf`), `.creer`/`.stocker` (`nom`, `matricule`, `civilite`, `agence_id`, `role`),
`.envoyer-pdf`, `.activer-desactiver`, `.reinitialiser-mot-de-passe`.
⚠️ **Anomalie** : `admin.agents.envoyer-pdf` produit l'URL `/admin/agents/agents/envoyer-pdf`
(segment `agents` dupliqué par erreur dans `routes/admin/agents.php`).

**Assistance** : `.assistant.repondre` (JSON).

**Audit** : `.journal-audit.index` (consultation), `.verifier-chaine` (action).

**Détection** : `.regles-detection.index`/`.basculer` (toggle actif/inactif), `.editer`/
`.mettre-a-jour` (`libelle`, `reference_texte`, `parametres[clé]` dynamique).

**Documents IA** : `.documents-ia.index` (checkboxes visibilité), `.creer`/`.stocker` (`documents[]`,
`portee`, `reseau_id`, `agence_id`, `visible_caissier`, `visible_responsable_agence`),
`.mettre-a-jour-visibilite`, `.supprimer`.

**Import** : `.import.index` (liste), `.creer` (`agence_id`, `fichier`), `.televerser` (étape
mapping, `mapping[entete]` dynamique), `.confirmer`.

**Listes** : `.listes.index`/`.importer` (`categorie`, `source`, `version`, `fichier`).

**PPE** : `.ppe.signataires.index` (consultation pure).

**Tableau de bord** : `.tableau-de-bord.index` (recherche `q`).

Routes purement JSON sans vue Blade dédiée (consommées en AJAX) : `agent.clients.lookup`,
`.npi.verifier`, `.telephone.verifier`, `.simulation-depot.verifier`, et les trois
`*.assistant.repondre`.

---

## 4. Points d'entrée où une décision humaine est prise

Un seul guard `agent` sert deux rôles (`RoleAgent::Caissier`, `RoleAgent::ResponsableAgence`) ; la
distinction de droit se fait par middleware de route (`role.agent:responsable_agence`) et, dans les
formulaires partagés (ex. `completer()`), par vérification explicite `estResponsableAgence()`.

| # | Décision | Fichier + méthode | Ce que fait l'action | Qui peut la déclencher |
|---|---|---|---|---|
| 1 | Complétion KYC | `Agent/Clients/ClientController::mettreAJour()` | Remplit la fiche, rejoue le filtrage, recalcule la complétude, journalise | Tout agent (caissier ou responsable) |
| 2 | Renseignement fiche RLBC/FT | même méthode, bloc conditionnel | Enregistre l'évaluation PPE/sanction/financement du terrorisme (dossier ou signataire) | **Responsable d'agence uniquement** — invisible/non modifiable pour un caissier |
| 3 | Création client depuis import OCR | `Agent/Clients/ImportDocumentController::valider()` | Clic explicite "Valider et créer" — jamais de création automatique après extraction | Tout agent |
| 4 | Décision sur correspondance de filtrage | `Responsable/Filtrage/FiltrageController::decider()` | Écarte/confirme, enregistre dans `MemoireDecisions`, met à jour statut PPE du client, **clôture automatiquement l'alerte liée** | Responsable d'agence uniquement |
| 5 | Traitement alerte NPI invalide | `Responsable/Conformite/AlerteNpiController::traiter()` | Marque l'alerte `Traitee`, journalise | Responsable d'agence uniquement |
| 6 | Génération déclaration CENTIF | `Responsable/Conformite/DeclarationCentifController::generer()` | Génère le gabarit, journalise | Responsable d'agence uniquement |
| 7 | Consultation KYC | `Responsable/Clients/ClientController::index()/afficher()` | Lecture seule, aucune écriture | Responsable d'agence |

**Constat important — pas de "gel/refus" comme décision humaine explicite.** Recherche exhaustive
de "geler"/"gel"/"refuser"/"refus" : aucune action de gel de compte pilotée par un clic humain
n'existe. Ce qui existe est un **blocage automatique par policy**, déclenché quand le caissier tente
l'opération : `Agent/Operations/OperationController::stocker()` refuse si pièce d'identité expirée
ou si `ClientPolicy::peutValiderOperation()` échoue (KYC bloquant manquant, signataire à vérifier,
alerte NPI non traitée). Le seul moyen de "débloquer" est de traiter les points 1, 2, 4, 5
ci-dessus — il n'y a pas d'écran dédié "geler le compte".

---

## 5. Outils IA déjà construits (`app/Services/Assistance/Outils/`)

Orchestrateur : `GestionnaireAssistant::traiter()` — contexte → provider (avec repli automatique sur
le simulateur) → filtre de sortie → journalisation → `array{reponse, source, peut_escalader}`. Le
jeu d'outils est construit **par rôle** (`OutilsParRole::pour()`) : un caissier reçoit 4 outils
communs (recherche documentaire, base de connaissances, recherche web, profils incomplets) ; le
responsable et l'admin reçoivent en plus 4 outils sensibles (alertes du jour, paramètre
réglementaire, client existant, statistiques agence). **Aucun function-calling actuellement** côté
simulateur : `RouteurOutilsMotsCles::router()` associe la question à l'outil par recoupement de
mots-clés, l'exécute, et **formate lui-même le texte affiché** (`match()` explicite par nom d'outil)
— le LLM ne rédige jamais la réponse finale à partir du résultat structuré en mode simulateur.

| Outil | Retour exact de `executer()` |
|---|---|
| OutilBaseConnaissancesProduit | `['trouve' => false]` ou `['trouve' => true, 'reponse' => string]` |
| OutilCompterAlertesDuJour | `['erreur' => string]` ou `['agence', 'critique', 'attention', 'info']` (comptage par gravité, `statut != traitee`) |
| OutilCompterProfilsIncomplets | `['erreur']` ou `['agence', 'nombre_total', 'dossiers' => [{'dossier': 'Dossier n°XXXXXXXX', 'champs_manquants'}] (max 15), 'repartition_anciennete']` (répartition réservée admin/responsable) |
| OutilConsulterParametreReglementaire | `['trouve' => false]` ou `['trouve' => true, 'code', 'libelle', 'parametres', 'source', 'reference_texte']` |
| OutilRechercheDocumentaire | `['resultats' => []]` ou `['resultats' => [{'document_id', 'titre', 'extrait' (≤1200 car.), 'page'}]]` (max 3) |
| OutilRechercherClientExistant | `['trouve' => false]` ou `['trouve' => bool]` **uniquement** — jamais le détail du résultat externe |
| OutilRechercheWeb | `['resultats' => []]` ou résultat brut de `SelecteurMoteurRechercheWeb` |
| OutilStatistiquesAgregeesAgence | `['erreur']` ou `['agence', 'nombre_dossiers', 'taux_completude_moyen', 'comptes_dormants_reactives_ce_mois']` |

`Concerns/ResoutAgenceOutil` (trait, pas un outil) : empêche qu'un agent fasse consulter une autre
agence via injection de prompt — résout toujours l'agence de l'agent courant, jamais un argument
fourni par l'IA.

---

## 6. Champs de texte libre du référentiel KYC

**Constat global** : il n'existe **aucun champ "commentaire"/"motif"/"origine des fonds" libre**
dans le référentiel actuel (recherche exhaustive sans résultat). Les seuls champs de texte libre
concernent l'activité professionnelle et quelques précisions descriptives — tous en `type_saisie =>
'text'` (`max:255`) dans `config/champs_fiche_adhesion.php`.

| Modèle | Colonne | Libellé | Chiffré ? |
|---|---|---|---|
| PersonnePhysique | `profession` | Profession | **Non** — colonne brute |
| PersonnePhysique | `employeur` | Employeur | **Non** |
| PersonnePhysique | `activite_1` / `activite_2` | Activité 1 / 2 | **Non** |
| PersonnePhysique | `indication_maison` | Précision localisation domicile | **Non** |
| PersonnePhysique | `indication_travail` | Indication lieu de travail | **Non** |
| PersonneMorale | `activite_1` / `activite_2` | Activité 1 / 2 | **Non** |
| PersonneMorale | `beneficiaire_effectif_texte` | Description bénéficiaire effectif (si pas de signataire désigné) | **Non** |
| Signataire | `fonction` | Fonction du signataire | **Non** |
| Mandataire | `lien_parente` | Lien de parenté | **Non** |

**Point notable pour la conformité** : contrairement aux champs d'identité structurés (nom,
prénoms, date de naissance, adresse, téléphone, email, NPI), qui utilisent systématiquement
`App\Casts\Chiffre`/`ChiffreIndexe`, **tous les champs de texte libre ci-dessus sont stockés en
clair**. Ce sont les champs matière première de la fonctionnalité §5 du prompt IA
(`activité`/`profession` notamment), mais ils ne bénéficient pas du même niveau de protection que
le reste de l'identité — à garder en tête pour la fonctionnalité "Lecteur de texte libre".

---

## 7. Constats transversaux (anomalies relevées pendant l'audit)

Ces points ne sont pas des suppositions : chacun a été vérifié dans le code par les agents
d'exploration.

1. **Aucun trait `AppartientAuReseau`** n'existe malgré la mention dans `CLAUDE.md` §5 — le scoping
   réseau/agence est géré table par table, manuellement.
2. **`entrees_liste`** stocke `npi`, `telephone`, `prenom`, `pays` en clair (pas de cast), alors que
   la convention du reste du schéma est "index aveugle seul" pour le NPI.
3. **`import_lignes.donnees_brutes`** (json) peut contenir de l'identité en clair issue du CSV
   importé, sans chiffrement au niveau de cette table tampon.
4. **`verifications_npi_en_attente.npi_chiffre`** est la seule colonne NPI chiffrée-récupérable de
   tout le schéma (exception documentée, nécessaire au nouvel essai de vérification).
5. **Route dupliquée** : `admin.agents.envoyer-pdf` produit `/admin/agents/agents/envoyer-pdf`
   (préfixe répété par erreur).
6. **`Empreinte/IndexBlocageMinHash`** est écrit et testé mais non branché dans le chemin de
   production (confirmé par son propre docblock).
7. **Pas d'écran de "gel de compte"** piloté par une décision humaine explicite — le blocage
   d'opération est une conséquence automatique de policy, pas un clic dédié (voir §4).
8. **Aucun champ "motif"/"origine des fonds" libre** n'existe dans le référentiel KYC actuel — la
   fonctionnalité §5 du prompt IA (typologies GIABA) devra s'appuyer sur `profession`/`activite_1`/
   `activite_2`/`indication_travail`, qui sont en clair (point 9).
9. Tous les champs de texte libre du référentiel KYC (§6 ci-dessus) sont **non chiffrés**,
   contrairement aux champs d'identité structurés.
10. `RouteurOutilsMotsCles` (simulateur hors connexion) **ne fait pas de function-calling** — il
    route par recoupement de mots-clés et formate lui-même la réponse ; le prompt §2.4 demande de
    déclarer la recherche web comme un vrai outil de function-calling **auprès de Gemini**, ce qui
    est un ajout côté `ProviderIaApiExterne`, distinct du chemin simulateur actuel.
11. Le prompt IA §5 s'appuie sur "la bibliothèque documentaire déjà construite" — elle existe bien
    (`DocumentIa`, `TraiteurDocumentIa`, `OutilRechercheDocumentaire`), avec un contrôle de
    visibilité par rôle (`visible_caissier`/`visible_responsable_agence`/`visible_administrateur`)
    et par réseau/agence (nullable = global).
12. Le prompt IA §6 s'appuie sur le `motif_ecart` enregistré à chaque écartement de correspondance
    de filtrage — il existe bien (`ResultatFiltrage.motif_ecart`, chiffré) et une mémoire de
    décisions existe déjà partiellement : `Filtrage/MemoireDecisions.php` + table
    `decisions_filtrage` (avec `motif_code` structuré ET `motif_detail` libre chiffré). La
    fonctionnalité 3 du prompt IA (regroupement de motifs en familles par IA) devra donc **étendre**
    ce mécanisme existant plutôt que d'en créer un nouveau depuis zéro.

---

## 8. Ce que ce document ne couvre pas encore

Conformément à l'étape 0, ce document recense l'état réel — il ne propose aucune implémentation.
Les points suivants restent à trancher avant de coder les fonctionnalités du prompt IA :
- Où brancher exactement le "copilote de saisie" (§4 du prompt) dans `_formulaire.blade.php` /
  `_champ.blade.php`, étant donné que ces vues sont pilotées par `config/champs_fiche_adhesion.php`
  et non par des champs statiques.
- Comment le filtre `FiltreConformiteReponseIa` et le contexte `ConstructeurContexteIa` existants
  doivent être étendus pour couvrir les nouveaux flux (caractéristiques dérivées du copilote,
  rapprochement de typologies, regroupement de motifs) sans jamais laisser passer d'identité.
- La décision `docs/DECISIONS.md` demandée par le prompt §6 sur le filtrage des motifs de
  `decisions_filtrage.motif_detail` avant tout envoi à un fournisseur externe.

**Ce document est le livrable de l'étape 0 — à valider avant toute ligne de code des
fonctionnalités 1, 2 et 3 du prompt.**

---

## 9. Mise à jour post-validation (2026-09-18)

Document validé par l'utilisateur. Quatre constats du §7 traités le jour même, voir
`docs/DECISIONS.md` §21 pour le détail complet de chaque décision :

- **Point #4 (route dupliquée `admin.agents.envoyer-pdf`) : corrigé.**
- **Point #1 (pas d'écran de gel de compte) : corrigé**, pas laissé en angle mort — voir §4
  ci-dessus, qui décrivait encore l'état *avant* cette correction (« pas de "gel/refus" comme
  décision humaine explicite »). Un écran minimal existe désormais côté responsable
  (`Responsable\Comptes\CompteController::geler()/lever()`), avec motif obligatoire et traçabilité
  complète (`comptes.gele_le`/`gele_par_agent_id`/`motif_gel`/`leve_le`/`leve_par_agent_id`).
- **Points #2 (texte libre en clair) et #3 (`entrees_liste` NPI/téléphone en clair) : documentés
  comme écarts assumés**, pas corrigés — risque de migration de rattrapage jugé disproportionné
  à quelques jours du gel de code. Le §6 ci-dessus reste donc exact tel quel.

Ordre d'implémentation retenu pour les fonctionnalités IA elles-mêmes (§4/§5/§6 du prompt) :
**3 (mémoire de décisions) → 1 (copilote de saisie) → 2 (lecteur de texte libre, optionnelle)** —
la fonctionnalité 3 s'appuie sur `Filtrage/MemoireDecisions.php` et `decisions_filtrage` déjà
existants (constat §7 point 12), ce qui en fait la moins coûteuse à finir malgré sa valeur la plus
forte à l'oral.
