<?php

namespace App\Services\Assistance;

use App\Models\Admin;
use App\Models\Agent;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

/**
 * Échanges conversationnels courants (salutations, politesse, « qui es-tu ») reconnus
 * AVANT le routage d'outils et la base de connaissances, pour que le simulateur (mode
 * démonstration, hors connexion, sans clé) ne réponde jamais « je n'ai pas d'information »
 * à un « salut ». Réponses fixes, variées, sans donnée métier ni identité.
 *
 * Volontairement strict : une salutation suivie d'une vraie question
 * (« bonjour, quels sont les profils incomplets ? ») n'est PAS traitée ici, la question
 * poursuit son chemin normal.
 */
class ReconnaisseurConversation
{
    private const SALUTATIONS = [
        'salut', 'bonjour', 'bonsoir', 'bjr', 'slt', 'cc', 'coucou', 'hey', 'hello', 'hi', 'yo',
        'salutations', 'bsr', 'wesh',
    ];

    /** Mots qui peuvent suivre une salutation (« bonjour à tous », « salut l'assistant »). */
    private const APRES_SALUTATION = ['assistant', 'a', 'tous', 'toi', 'vous', 'les', 'gars', 'l'];

    private const IDENTITE = [
        'qui es tu', 'tu es qui', 'qui etes vous', 'vous etes qui', 'tu es quoi', 'tu es quoi exactement',
        'tu fais quoi', 'que fais tu', 'tu sers a quoi', 'a quoi tu sers', 'ca sert a quoi', 'a quoi ca sert',
        'a quoi sers tu', 'aide', 'aide moi', 'aidez moi', 'help', 'que peux tu faire', 'que sais tu faire',
        'que peut on te demander', 'presente toi', 'presentez vous', 'tu peux faire quoi', 'tu peux m aider',
        'peux tu m aider', 'comment tu peux m aider', 'comment peux tu m aider', 'tu peux aider',
    ];

    private const ETAT = [
        'ca va', 'ca va bien', 'cava', 'comment vas tu', 'comment tu vas', 'comment allez vous', 'tu vas bien',
        'vous allez bien', 'la forme', 'comment ca va', 'tu vas bien ou quoi', 'quoi de neuf', 'ca roule',
    ];

    private const REMERCIEMENTS = [
        'merci', 'merci beaucoup', 'mci', 'merci bien', 'thanks', 'thank you', 'grand merci', 'merci a toi',
        'merci infiniment',
    ];

    private const ACQUIESCEMENTS = [
        'ok', 'okay', 'ok merci', 'd accord', 'dacc', 'dac', 'super', 'parfait', 'top', 'bien recu', 'compris',
        'cool', 'nickel', 'genial', 'entendu', 'tres bien', 'bien', 'oui', 'yes',
    ];

    private const AU_REVOIR = [
        'au revoir', 'bye', 'a plus', 'a plus tard', 'a bientot', 'adieu', 'bonne journee', 'bonne soiree',
        'a demain', 'ciao', 'a la prochaine', 'bonne continuation', 'bonne fin de journee',
    ];

    /**
     * @return string|null réponse conversationnelle, ou null si la question est une vraie question métier
     */
    public function repondre(string $question, Agent|Admin $utilisateur): ?string
    {
        $mots = explode(' ', $this->normaliser($question));
        $mots = array_values(array_filter($mots, fn (string $m) => $m !== ''));

        if ($mots === []) {
            return null;
        }

        $premier = $mots[0];
        $salue = false;

        // Retire les salutations initiales (« salut », « bonjour l'assistant », « cc salut »).
        while ($mots !== []) {
            if (in_array($mots[0], self::SALUTATIONS, true)) {
                $salue = true;
            } elseif (! ($salue && in_array($mots[0], self::APRES_SALUTATION, true))) {
                break;
            }

            array_shift($mots);
        }

        // Retire le remplissage final (« merci svp », « aide moi stp »).
        while ($mots !== [] && in_array(end($mots), ['svp', 'stp', 'please', 'assistant'], true)) {
            array_pop($mots);
        }

        $reste = implode(' ', $mots);

        if ($reste === '') {
            return $salue ? $this->salutation($premier, $utilisateur) : null;
        }

        return match (true) {
            in_array($reste, self::IDENTITE, true) => $this->presentation($utilisateur),
            in_array($reste, self::ETAT, true) => $this->etat($utilisateur),
            in_array($reste, self::REMERCIEMENTS, true) => Arr::random([
                'Avec plaisir ! N\'hésitez pas si vous avez une autre question.',
                'Je vous en prie. Je reste disponible pour la suite.',
                'De rien, c\'est fait pour ça.',
            ]),
            in_array($reste, self::ACQUIESCEMENTS, true) => Arr::random([
                'Très bien. Dites-moi si vous avez une autre question.',
                'D\'accord. Je suis là si besoin.',
                'Parfait, n\'hésitez pas à me solliciter.',
            ]),
            in_array($reste, self::AU_REVOIR, true) => Arr::random([
                'Au revoir, et bonne continuation !',
                'À bientôt ! Je reste disponible quand vous voulez.',
                'À plus tard, bon travail !',
            ]),
            default => null,
        };
    }

    /**
     * Minuscules, sans accents, ponctuation retirée, espaces réduits, lettres répétées
     * 3 fois ou plus ramenées à une (« saluuuut » → « salut »).
     */
    public function normaliser(string $texte): string
    {
        $ascii = Str::of($texte)->ascii()->lower()->toString();
        $ascii = preg_replace('/([a-z])\1{2,}/', '$1', $ascii) ?? '';
        $ascii = preg_replace('/[^a-z0-9]+/', ' ', $ascii) ?? '';

        return trim($ascii);
    }

    private function salutation(string $mot, Agent|Admin $utilisateur): string
    {
        $accueil = in_array($mot, ['bonsoir', 'bsr'], true)
            ? Arr::random(['Bonsoir !', 'Bonsoir, ravi de vous retrouver !'])
            : Arr::random(['Bonjour !', 'Salut !', 'Bonjour, ravi de vous voir !', 'Hello !']);

        return $accueil.' '.$this->resumePerimetre($utilisateur);
    }

    private function etat(Agent|Admin $utilisateur): string
    {
        return Arr::random([
            'Très bien, merci ! ',
            'Tout va bien de mon côté, merci de demander. ',
            'En pleine forme ! ',
        ]).'Comment puis-je vous aider ? '.$this->exemples($utilisateur);
    }

    private function presentation(Agent|Admin $utilisateur): string
    {
        return Arr::random([
            'Je suis l\'assistant de l\'application. ',
            'Je suis votre assistant de conformité intégré à l\'application. ',
        ])
            .'Je peux expliquer un champ de la fiche KYC, un concept réglementaire ('.$this->conceptsCites($utilisateur).') '
            .'ou vous guider dans l\'application. '.$this->exemples($utilisateur);
    }

    private function resumePerimetre(Agent|Admin $utilisateur): string
    {
        return Arr::random([
            'Je peux vous expliquer un champ KYC, un concept réglementaire ou vous guider dans l\'application. ',
            'Je suis là pour éclairer un champ de la fiche client, une notion réglementaire ou un écran de l\'application. ',
        ]).$this->exemples($utilisateur);
    }

    /**
     * Le caissier ne doit jamais voir certains termes (Loi art. 63, Support\MotsInterditsConformite) :
     * ses textes — exemples compris — n'en contiennent aucun, sinon le filtre de sortie
     * remplacerait un simple « salut » par le message d'escalade.
     */
    private function estCaissier(Agent|Admin $utilisateur): bool
    {
        return $utilisateur instanceof Agent && $utilisateur->estCaissier();
    }

    private function conceptsCites(Agent|Admin $utilisateur): string
    {
        return $this->estCaissier($utilisateur) ? 'SFD, bénéficiaire effectif…' : 'PPE, bénéficiaire effectif, DOS…';
    }

    private function exemples(Agent|Admin $utilisateur): string
    {
        $exemples = $this->estCaissier($utilisateur)
            ? ['Qu\'est-ce qu\'un score de complétude KYC ?', 'Quels sont les profils incomplets ?', 'Comment créer un client sans connexion ?']
            : ['Combien d\'alertes aujourd\'hui ?', 'Quels sont les profils incomplets ?', 'Que veut dire PPE ?'];

        return 'Par exemple : « '.implode(' », « ', $exemples).' »';
    }
}
