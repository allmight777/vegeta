<?php

/**
 * Durée de validité d'une décision de filtrage. Hypothèse de démonstration :
 * une décision d'homonymie reste vraie longtemps, mais jamais indéfiniment — les
 * listes évoluent et une personne peut être ajoutée après coup.
 */
return [
    'decisions' => [
        'duree_jours' => 180,
        'source' => 'demo',
        'reference_texte' => 'Durée interne de revalidation des faux positifs — à confirmer avec les mentors',
    ],
];