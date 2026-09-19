<?php

namespace Tests\Unit\Kyc;

use App\Services\Kyc\DetecteurIncoherencesSaisie;
use Tests\TestCase;

class DetecteurIncoherencesSaisieTest extends TestCase
{
    public function test_un_age_incompatible_avec_une_profession_retraitee_est_signale(): void
    {
        $resultat = app(DetecteurIncoherencesSaisie::class)->verifier(15, 'Retraité', null, false);

        $this->assertCount(1, $resultat['avertissements']);
        $this->assertSame('profession', $resultat['avertissements'][0]['champ']);
        $this->assertSame('regles_php', $resultat['source']);
    }

    public function test_une_profession_retraitee_a_un_age_plausible_nest_pas_signalee(): void
    {
        $resultat = app(DetecteurIncoherencesSaisie::class)->verifier(68, 'Retraitée de la fonction publique', null, false);

        $this->assertSame([], $resultat['avertissements']);
    }

    public function test_un_ratio_depot_revenu_eleve_est_signale(): void
    {
        // 5 000 000 XOF déposés pour un revenu mensuel déclaré de 50 000 XOF (ratio 100).
        $resultat = app(DetecteurIncoherencesSaisie::class)->verifier(35, 'Commerçante', 100.0, false);

        $this->assertCount(1, $resultat['avertissements']);
        $this->assertSame('revenus_mensuels_estimes', $resultat['avertissements'][0]['champ']);
    }

    public function test_un_ratio_depot_revenu_raisonnable_nest_pas_signale(): void
    {
        $resultat = app(DetecteurIncoherencesSaisie::class)->verifier(35, 'Commerçante', 3.0, false);

        $this->assertSame([], $resultat['avertissements']);
    }

    public function test_une_piece_didentite_expiree_est_signalee(): void
    {
        $resultat = app(DetecteurIncoherencesSaisie::class)->verifier(null, null, null, true);

        $this->assertCount(1, $resultat['avertissements']);
        $this->assertSame('piece_identite_expiration', $resultat['avertissements'][0]['champ']);
    }

    public function test_plusieurs_incoherences_peuvent_etre_signalees_ensemble(): void
    {
        $resultat = app(DetecteurIncoherencesSaisie::class)->verifier(15, 'Retraité', 100.0, true);

        $this->assertCount(3, $resultat['avertissements']);
    }

    public function test_aucune_caracteristique_ne_produit_aucun_avertissement(): void
    {
        $resultat = app(DetecteurIncoherencesSaisie::class)->verifier(null, null, null, false);

        $this->assertSame([], $resultat['avertissements']);
    }
}
