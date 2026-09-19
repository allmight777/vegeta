<!--
Base de connaissances de l'assistant IA (08_PROMPT_ASSISTANT_IA_CONFORMITE §6).
Format : "## [source] Question" suivi du texte de réponse, jusqu'au prochain "##".
source ∈ reglementaire | produit | reponse_responsable (cette dernière n'est jamais
écrite ici — elle vient des lignes escalades_assistant_ia traitées, voir
Services\Assistance\BaseConnaissances). Ne jamais aller au-delà de ce qui est écrit
dans CLAUDE.md : une question plus précise est un cas d'escalade, pas une occasion
d'improviser.
-->

## [reglementaire] Que veut dire SFD ?

SFD signifie système financier décentralisé — c'est le type d'institution (caisse,
mutuelle, coopérative d'épargne et de crédit) auquel s'adresse CIF-Empreinte en Afrique
de l'Ouest.

## [reglementaire] Que veut dire RLBC/FT ?

RLBC/FT désigne le responsable de la lutte contre le blanchiment de capitaux et le
financement du terrorisme au sein de l'institution — la personne habilitée à autoriser
certaines décisions de conformité (voir CLAUDE.md §3, colonne Fondement).

## [reglementaire] Que veut dire CENTIF ?

La CENTIF est la cellule nationale de traitement des informations financières — l'autorité
qui reçoit les déclarations d'opération suspecte (DOS) et les déclarations de transactions
en espèces (DTE).

## [reglementaire] C'est quoi une DOS ?

Une DOS est une déclaration d'opération suspecte, transmise à la CENTIF. Sa divulgation à
un tiers, y compris au client concerné, est interdite par la loi (Loi art. 63) — c'est
pourquoi le rôle guichet ne voit jamais ce mot ni le contenu d'un tel dossier.

## [reglementaire] Que veut dire PPE ?

PPE signifie personne politiquement exposée : une personne occupant ou ayant occupé une
fonction publique importante (nationale, étrangère, dans une organisation internationale),
sa famille ou ses proches associés. Un client PPE fait l'objet d'une surveillance renforcée,
d'une autorisation de la haute direction et d'une réévaluation périodique.

## [reglementaire] Qu'est-ce qu'un bénéficiaire effectif ?

Le bénéficiaire effectif d'une personne morale est la personne physique qui détient,
directement ou indirectement, plus de 25 % du capital ou des droits de vote, ou qui exerce
un contrôle par un autre moyen ; à défaut, c'est le dirigeant principal qui est retenu.

## [reglementaire] Pourquoi ce compte est-il bloqué ?

Un gel conservatoire peut être appliqué automatiquement lorsqu'une correspondance forte est
détectée contre une liste de sanctions ou un profil à risque. La mesure doit ensuite être
confirmée par un agent habilité — l'outil ne fait que recommander, la décision reste
humaine. Pour le détail d'un dossier précis, seul le responsable conformité peut vous
répondre.

## [produit] Qu'est-ce qu'un score de complétude KYC ?

Le score de complétude indique la proportion des champs attendus par le référentiel KYC déjà
renseignés pour ce client. Un champ marqué comme bloquant empêche la validation d'une
opération tant qu'il n'est pas complété — les autres champs comptent dans le score affiché
mais ne bloquent rien.

## [produit] Pourquoi dois-je remplir le lieu de naissance ?

Ce champ fait partie du référentiel de la fiche d'adhésion CIF ; il aide à distinguer deux
personnes portant un nom proche lors du rapprochement d'identité. Consultez le badge à côté
du champ pour connaître sa source (réglementaire, briefing CIF ou politique interne).

## [produit] Comment créer un client sans connexion ?

Le formulaire de création fonctionne normalement hors connexion. Si un NPI est saisi, il est
accepté avec un format plausible et mis en attente de vérification automatique dès le retour
de la connexion (bandeau « en attente de connexion » sur la fiche) — la création n'est
jamais bloquée par une simple coupure réseau.

## [produit] Comment uploader plusieurs fiches d'un coup ?

Depuis la liste des clients, le bouton « Uploader des fiches » permet de déposer jusqu'à 10
documents (PDF, Word, ou photo) en une fois. Chaque document est traité indépendamment ;
l'écran de revue qui suit affiche un badge de méthode d'extraction et un niveau de
confiance par champ, à valider un dossier à la fois — rien n'est créé automatiquement.

## [produit] Où je trouve le tableau de bord ?

Le tableau de bord est accessible depuis le menu principal de l'espace agent. Son contenu
dépend du rôle connecté : un agent guichet voit une version simplifiée (opérations du jour,
dossiers à compléter), un responsable LBC/FT ou la direction voient en plus les alertes, les
seuils de fractionnement, les déclarations CENTIF à venir et les NPI en attente de
vérification.

## [produit] Comment compléter la fiche d'un client existant ?

Depuis la liste des clients, ouvrez la fiche à compléter : les champs déjà surlignés en
jaune sont ceux qui manquent. Le bouton « Compléter depuis le système existant » recherche
automatiquement une correspondance déjà connue (par NPI ou par nom et date de naissance) et
propose de reprendre uniquement les champs que vous cochez.

## [produit] Que veut dire le badge de source à côté d'un champ ?

Il indique d'où vient la règle appliquée à ce champ : réglementaire (un texte de loi cité),
briefing CIF (annoncé oralement par les experts, à confirmer), politique interne
(modifiable par l'institution), ou démonstration (hypothèse à valider). C'est la même
transparence que celle appliquée aux seuils de détection.

## [reglementaire] Quelles sont les lois et textes des finances applicables (loi, loi uniforme, BCEAO, réglementation) ?

Deux textes encadrent la lutte contre le blanchiment, le financement du terrorisme et de la
prolifération (LBC/FT/FP) dans l'application : la Loi uniforme LBC/FT/FP de l'UMOA (2023) et
l'Instruction BCEAO n°001-03-2025. Ils imposent notamment d'identifier le client (art. 17),
de conserver les données 10 ans (art. 23), de traiter les PPE avec une vigilance renforcée
(art. 29) et de geler sans délai les avoirs listés (art. 89 à 91). Les seuils chiffrés sont
fixés par l'autorité compétente : l'application les lit dans ses paramètres, jamais en dur.
Pour un avis juridique précis, utilisez « Transmettre à un responsable ».

## [reglementaire] Que dit la loi sur les clients avec un profil incomplet, une fiche incomplète ou des informations manquantes ?

Un client dont la fiche KYC est incomplète (score de complétude inférieur à 100 %) ne peut pas
faire d'opération tant que les champs bloquants manquent. Le profil doit être mis à jour sous
1 mois après toute nouvelle information (Instruction BCEAO n°001-03-2025, art. 6). Pour
retrouver ces clients : menu Clients, filtre « À compléter » ; les champs manquants sont
signalés en rouge dans la fiche. Complétez-les puis enregistrez.

## [produit] Pourquoi le NPI, le téléphone et l'email sont-ils obligatoires ?

Sur la fiche d'une personne physique, le Numéro personnel d'identification (NPI), le
téléphone et l'email sont obligatoires : ils servent à identifier le client de façon fiable et
à le joindre. Un champ vide ou invalide est entouré en rouge avec un message ; remplissez-le
puis cliquez sur « Vérifier et continuer ».

## [produit] Que signifie « Corrigez les champs suivants » sur la fiche client ?

Un ou plusieurs champs obligatoires sont vides ou incorrects. Chaque champ concerné est entouré
en rouge avec la raison sous le champ. Corrigez-les, puis enregistrez de nouveau.
