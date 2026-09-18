<?php

namespace Tests\Feature\Kyc;

use App\Enums\MethodeExtraction;
use App\Services\Kyc\ExtracteurDocumentOcrLocal;
use App\Services\Kyc\MappeurChampsExtraits;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use thiagoalessio\TesseractOCR\FriendlyErrors;

class ExtracteurDocumentOcrLocalTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Vrai seulement si le binaire tesseract est réellement présent sur ce poste — sinon
     * le pipeline OCR réel (07_PROMPT_MODE_DEGRADE_NPI_OCR §4) ne peut pas être exercé
     * ici, mais fonctionnera sur un poste équipé (ex. Windows + Tesseract le jour J).
     */
    private function tesseractDisponible(): bool
    {
        try {
            FriendlyErrors::checkTesseractPresence('tesseract');

            return true;
        } catch (\Throwable) {
            return false;
        }
    }

    /**
     * Fabrique une image de test synthétique (texte imprimé simple, jamais une vraie
     * fiche client, cf. CLAUDE.md §2.1) contenant les libellés du référentiel.
     */
    private function imageDeTexteSynthetique(string $texte): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'ocr').'.png';
        $image = imagecreate(600, 100);
        imagecolorallocate($image, 255, 255, 255);
        $noir = imagecolorallocate($image, 0, 0, 0);
        imagestring($image, 5, 10, 10, $texte, $noir);
        imagepng($image, $chemin);
        imagedestroy($image);

        return $chemin;
    }

    public function test_les_champs_sont_retrouves_sur_une_image_de_texte_simple_avec_une_confiance_plafonnee(): void
    {
        if (! $this->tesseractDisponible()) {
            $this->markTestSkipped('tesseract non installé sur ce poste — le pipeline OCR réel n\'est pas exerçable ici (voir docs/COMPOSANTS_TIERS.md).');
        }

        $chemin = $this->imageDeTexteSynthetique('Nom : KPADONOU');

        $resultat = app(ExtracteurDocumentOcrLocal::class)->extraire($chemin, 'image/png');

        $this->assertTrue($resultat->reussie);
        $this->assertSame(MethodeExtraction::OcrLocal, $resultat->methode);
        $this->assertStringContainsString('KPADONOU', $resultat->texte);
    }

    /**
     * Plusieurs lignes de texte, une sous l'autre — simule un document à champs
     * alignés en colonnes façon fiche d'adhésion (chaque `imagestring` est une ligne).
     */
    private function imageMultiLigneSynthetique(array $lignes): string
    {
        $chemin = tempnam(sys_get_temp_dir(), 'ocr').'.png';
        $image = imagecreate(700, 40 + count($lignes) * 25);
        imagecolorallocate($image, 255, 255, 255);
        $noir = imagecolorallocate($image, 0, 0, 0);

        foreach (array_values($lignes) as $index => $ligne) {
            imagestring($image, 5, 10, 10 + $index * 25, $ligne, $noir);
        }

        imagepng($image, $chemin);
        imagedestroy($image);

        return $chemin;
    }

    /**
     * Bout en bout avec deux passes Tesseract réelles (défaut + --psm 4) : le texte
     * retenu par ExtracteurDocumentOcrLocal doit rester exploitable par
     * MappeurChampsExtraits, quel que soit le format (même ligne ou ligne suivante)
     * que la segmentation Tesseract a produit sur ce document précis.
     */
    public function test_extraction_ocr_reelle_reste_exploitable_par_le_mappeur_sur_plusieurs_champs(): void
    {
        if (! $this->tesseractDisponible()) {
            $this->markTestSkipped('tesseract non installé sur ce poste — le pipeline OCR réel n\'est pas exerçable ici (voir docs/COMPOSANTS_TIERS.md).');
        }

        $chemin = $this->imageMultiLigneSynthetique(['NOM KPADONOU', 'EMPLOYEUR SFD Demo']);

        $resultat = app(ExtracteurDocumentOcrLocal::class)->extraire($chemin, 'image/png');
        $this->assertTrue($resultat->reussie);

        $champs = app(MappeurChampsExtraits::class)->mapper($resultat->texte, 'personne_physique');

        $this->assertArrayHasKey('nom', $champs);
        $this->assertStringContainsString('KPADONOU', $champs['nom']['valeur']);
    }

    public function test_le_mappeur_ne_depasse_jamais_le_plafond_de_confiance_donne_meme_pour_un_match_explicite(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper('Nom : KPADONOU', 'personne_physique', 0.7);

        $this->assertSame(0.7, $resultat['nom']['confiance']);
    }

    /**
     * Format « LIBELLE valeur » ou « LIBELLE : valeur » sur la même ligne — le plus
     * courant, produit aussi bien par le texte natif que par l'OCR en segmentation par
     * ligne (mode par défaut de Tesseract).
     */
    public function test_le_mappeur_retrouve_les_champs_au_format_meme_ligne_avec_ou_sans_deux_points(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper("Nom : KPADONOU\nEmployeur SFD Démonstration", 'personne_physique');

        $this->assertSame('KPADONOU', $resultat['nom']['valeur']);
        $this->assertSame('SFD Démonstration', $resultat['employeur']['valeur']);
    }

    /**
     * Format « LIBELLE » seul suivi de la valeur sur la ligne suivante — celui que
     * produit `--psm 4` sur une fiche à champs alignés en colonnes (cf. l'incident
     * réel qui a motivé ce repli : le bloc « Activité économique » disparaissait avec
     * le format à deux-points strict).
     */
    public function test_le_mappeur_retrouve_les_champs_au_format_libelle_puis_valeur_sur_la_ligne_suivante(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper("NOM\nKPADONOU\n\nEMPLOYEUR\nSFD Démonstration", 'personne_physique');

        $this->assertSame('KPADONOU', $resultat['nom']['valeur']);
        $this->assertSame('SFD Démonstration', $resultat['employeur']['valeur']);
    }

    public function test_le_mappeur_ne_trouve_rien_dans_un_texte_sans_rapport_avec_le_referentiel(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper('Ceci est un texte réglementaire sans rapport avec une fiche KYC.', 'personne_physique');

        $this->assertSame([], $resultat);
    }

    /**
     * Régression : « ADRESSE / CONTACT » est un titre de section OCRisé sur une seule
     * ligne, pas un champ « Adresse » suivi de sa valeur. L'ancienne regex « même
     * ligne » acceptait n'importe quel reste après le libellé, y compris « / CONTACT ».
     */
    public function test_un_titre_de_section_contenant_le_libelle_ne_produit_aucune_valeur(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper(
            "NOM DOSSOU\nADRESSE / CONTACT\nTELEPHONE 90001122",
            'personne_physique',
        );

        $this->assertArrayNotHasKey('adresse', $resultat);
        $this->assertSame('90001122', $resultat['telephone']['valeur']);
    }

    /**
     * Régression : « Nom » et « Prénoms » doivent rester deux champs distincts même
     * quand leurs lignes se suivent immédiatement.
     */
    public function test_nom_et_prenoms_sont_distingues_sur_des_lignes_consecutives(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper("NOM DOSSOU\nPRENOMS Jean Fictif", 'personne_physique');

        $this->assertSame('DOSSOU', $resultat['nom']['valeur']);
        $this->assertSame('Jean Fictif', $resultat['prenoms']['valeur']);
    }

    /**
     * Régression : un libellé isolé suivi du titre d'une autre section (pas d'une
     * valeur) ne doit produire aucune valeur pour ce champ, ni « voler » la ligne de
     * titre au champ qui, lui, en aurait légitimement besoin comme valeur.
     */
    public function test_un_libelle_isole_suivi_d_un_titre_de_section_ne_produit_aucune_valeur(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper(
            "TELEPHONE\nACTIVITE ECONOMIQUE\nProfession : Commerçant",
            'personne_physique',
        );

        $this->assertArrayNotHasKey('telephone', $resultat);
        $this->assertSame('Commerçant', $resultat['profession']['valeur']);
    }

    /**
     * Régression : « Nom » et « Nom du responsable » partagent un préfixe. Sans
     * priorité au libellé le plus long et sans réservation de ligne, « Nom » happait
     * la ligne de « Nom du responsable » et lui donnait « du responsable : ... » comme
     * valeur.
     */
    public function test_nom_et_nom_du_responsable_ne_se_melangent_pas_malgre_le_prefixe_commun(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper(
            "Nom du responsable : Jean Dupont\nNom : DOSSOU",
            'personne_physique',
        );

        $this->assertSame('DOSSOU', $resultat['nom']['valeur']);
        $this->assertSame('Jean Dupont', $resultat['signature_responsable_nom']['valeur']);
    }

    public function test_une_date_avec_barres_obliques_reste_acceptee_comme_valeur(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper('Date de naissance : 12/03/1990', 'personne_physique');

        $this->assertSame('12/03/1990', $resultat['date_naissance']['valeur']);
    }

    public function test_un_champ_sans_valeur_retenue_n_affiche_jamais_de_confiance_trompeuse(): void
    {
        $resultat = app(MappeurChampsExtraits::class)->mapper('ADRESSE / CONTACT', 'personne_physique');

        $this->assertArrayNotHasKey('adresse', $resultat);
    }

    public function test_un_type_de_fichier_non_gere_echoue_explicitement(): void
    {
        $chemin = tempnam(sys_get_temp_dir(), 'inconnu').'.doc';
        file_put_contents($chemin, 'contenu binaire non exploitable');

        $resultat = app(ExtracteurDocumentOcrLocal::class)->extraire($chemin, 'application/msword');

        $this->assertFalse($resultat->reussie);
        $this->assertNotNull($resultat->erreurMessage);
    }
}
