<?php

namespace Tests\Unit\Empreinte;

use App\Services\Empreinte\ComparateurEmpreinte;
use App\Services\Empreinte\GenerateurEmpreinte;
use App\Services\Empreinte\IndexBlocageMinHash;
use App\Services\Securite\GestionnaireCles;
use Tests\TestCase;

class GenerateurEmpreinteTest extends TestCase
{
    private GenerateurEmpreinte $generateur;

    private ComparateurEmpreinte $comparateur;

    protected function setUp(): void
    {
        parent::setUp();
        $this->generateur = app(GenerateurEmpreinte::class);
        $this->comparateur = app(ComparateurEmpreinte::class);
    }

    private function diceNom(string $a, string $b): float
    {
        return $this->comparateur->dice(
            $this->generateur->encoderNom($a),
            $this->generateur->encoderNom($b),
        );
    }

    public function test_une_variante_orthographique_reste_proche(): void
    {
        $score = $this->diceNom('KPADONOU Fidèle', 'KPADENOU Fidel');

        $this->assertGreaterThanOrEqual(0.72, $score);
        $this->assertLessThanOrEqual(0.90, $score);
    }

    public function test_inversion_nom_prenom_sans_accent_est_neutralisee(): void
    {
        $score = $this->diceNom('KPADONOU Fidèle', 'Fidele Kpadonou');

        $this->assertEqualsWithDelta(1.0, $score, 0.0001);
    }

    public function test_deux_noms_sans_rapport_sont_eloignes(): void
    {
        $score = $this->diceNom('KPADONOU Fidèle', 'AHOUANDJINOU Rachidatou');

        $this->assertLessThan(0.45, $score);
    }

    public function test_score_composite_variante_plus_meme_date_naissance_depasse_le_seuil_lien_automatique(): void
    {
        $diceNom = $this->diceNom('KPADONOU Fidèle', 'KPADENOU Fidel');

        $dateA = $this->generateur->encoderDate($this->generateur->normaliserDate('1990-05-12'));
        $dateB = $this->generateur->encoderDate($this->generateur->normaliserDate('1990-05-12'));
        $diceDate = $this->comparateur->dice($dateA, $dateB);

        $score = $this->comparateur->scoreComposite($diceNom, $diceDate);

        $this->assertGreaterThanOrEqual(0.85, $score);
    }

    public function test_nom_proche_mais_dates_differentes_ne_depasse_pas_le_seuil(): void
    {
        $diceNom = $this->diceNom('AGBO Paul', 'AGBO Pauline');

        $dateA = $this->generateur->encoderDate($this->generateur->normaliserDate('1980-01-01'));
        $dateB = $this->generateur->encoderDate($this->generateur->normaliserDate('1995-11-20'));
        $diceDate = $this->comparateur->dice($dateA, $dateB);

        $score = $this->comparateur->scoreComposite($diceNom, $diceDate);

        $this->assertLessThan(0.85, $score);
    }

    public function test_meme_entree_meme_cle_donne_un_vecteur_identique(): void
    {
        $a = $this->generateur->encoderNom('KPADONOU Fidèle');
        $b = $this->generateur->encoderNom('KPADONOU Fidèle');

        $this->assertSame($a->versOctets(), $b->versOctets());
    }

    public function test_le_vecteur_ne_contient_aucune_sous_chaine_du_nom(): void
    {
        $vecteur = $this->generateur->encoderNom('KPADONOU Fidèle');

        $this->assertStringNotContainsString('KPADONOU', $vecteur->versOctets());
        $this->assertStringNotContainsString('FIDELE', strtoupper($vecteur->versOctets()));
    }

    public function test_le_blocage_minhash_partage_au_moins_une_bande_pour_une_variante(): void
    {
        $blocage = app(IndexBlocageMinHash::class);

        $vecteurA = $this->generateur->encoderNom('KPADONOU Fidèle');
        $vecteurB = $this->generateur->encoderNom('KPADENOU Fidel');

        $signatureA = $blocage->signature($vecteurA);
        $candidats = $blocage->candidatsPartageantUneBande($signatureA, ['variante' => $blocage->signature($vecteurB)]);

        $this->assertContains('variante', $candidats);
    }

    public function test_une_cle_differente_change_le_vecteur(): void
    {
        $vecteurCleA = $this->generateur->encoderNom('KPADONOU Fidèle');

        config(['cles.demo' => base64_encode(random_bytes(32))]);
        $vecteurCleB = (new GenerateurEmpreinte(new GestionnaireCles))->encoderNom('KPADONOU Fidèle');

        $score = $this->comparateur->dice($vecteurCleA, $vecteurCleB);

        $this->assertLessThan(0.5, $score);
    }
}
