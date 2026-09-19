<?php

namespace App\Services\Coherence;

use App\Enums\StatutAlerte;
use App\Enums\TypeAlerte;
use App\Models\Alerte;
use App\Models\Client;
use App\Services\Coherence\Indicateurs\Indicateur;

/**
 * Vigilance constante (problème 7) : confronte le profil déclaré au KYC au
 * comportement transactionnel réellement observé.
 *
 * Le filtrage sanctions/PPE protège l'entrée en relation ; il ne dit rien de
 * ce qui se passe ensuite. Or la Loi uniforme (art. 18) et l'Instruction BCEAO
 * 001-03-2025 (art. 6) imposent de vérifier que les opérations restent
 * cohérentes avec la connaissance du client. Un seuil absolu ne sait pas qu'un
 * dépôt de 400 000 XOF est banal pour un grossiste et aberrant pour un
 * apprenti tailleur : seul l'écart au profil le dit.
 *
 * Destinataire : le RESPONSABLE D'AGENCE, jamais le caissier. Le caissier ne
 * doit pas savoir qu'un membre est sous analyse (Loi art. 63, non-divulgation)
 * — c'est la même règle que pour le filtrage, et elle est portée par les
 * écrans existants.
 */
class AnalyseurCoherenceProfil
{
    /** @var array<int, Indicateur> */
    private array $indicateurs;

    public function __construct(Indicateur ...$indicateurs)
    {
        $this->indicateurs = $indicateurs;
    }

    /**
     * Analyse un client et renvoie son faisceau, qu'il soit constitué ou non.
     * N'écrit rien : c'est `signaler()` qui décide d'alerter.
     */
    public function analyser(Client $client): FaisceauIndices
    {
        $fenetreMax = max(
            (int) config('coherence.ecart_flux_revenus.fenetre_jours'),
            (int) config('coherence.compte_de_passage.fenetre_jours'),
        );

        $contexte = ContexteTransactionnel::pour($client, $fenetreMax);
        $faisceau = new FaisceauIndices;

        foreach ($this->indicateurs as $indicateur) {
            $faisceau->ajouter($indicateur->evaluer($client, $contexte));
        }

        return $faisceau;
    }

    /**
     * Analyse puis, si le faisceau est constitué, ouvre une alerte à
     * destination du responsable d'agence.
     *
     * Idempotent : tant qu'une alerte ouverte porte la même signature de
     * faisceau, on n'en crée pas de seconde. Sans ça, une campagne nocturne
     * rouvrirait chaque nuit le même dossier et le responsable cesserait de
     * lire sa file.
     */
    public function signaler(Client $client): ?Alerte
    {
        $faisceau = $this->analyser($client);

        if (! $faisceau->estConstitue()) {
            return null;
        }

        if ($this->dejaSignale($client, $faisceau)) {
            return null;
        }

        return Alerte::create([
            'type' => TypeAlerte::IncoherenceProfil,
            'client_id' => $client->id,
            'agence_id' => $client->agence_creation_id,
            'gravite' => $faisceau->gravite(),
            'explication_texte' => app(NarrateurFaisceau::class)->raconter($faisceau),
            'faits' => $faisceau->versFaits(),
            'statut' => StatutAlerte::Nouvelle,
        ]);
    }

    private function dejaSignale(Client $client, FaisceauIndices $faisceau): bool
    {
        return Alerte::query()
            ->where('client_id', $client->id)
            ->where('type', TypeAlerte::IncoherenceProfil)
            ->where('statut', StatutAlerte::Nouvelle)
            ->get()
            ->contains(fn (Alerte $alerte) => ($alerte->faits['signature'] ?? null) === $faisceau->signature());
    }
}
