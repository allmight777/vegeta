<?php

namespace App\Services\Assistance;

use RuntimeException;

/**
 * Levée quand tous les modèles de repli du fournisseur externe ont échoué —
 * App\Services\Assistance\GestionnaireAssistant la catch pour basculer sur le
 * simulateur, jamais laisser une erreur brute atteindre l'agent.
 */
class ProviderIaIndisponibleException extends RuntimeException {}
