<?php

namespace App\Services\Listes;

use Maatwebsite\Excel\Concerns\Import;

/**
 * Classe vide implémentant Import — sert uniquement de type valide pour
 * Excel::toArray(new NoopImport, $fichier), qui n'a besoin d'aucune logique
 * d'import (on lit le tableau brut et on gère l'insertion manuellement pour
 * contrôler précisément les colonnes et les valeurs vides).
 */
class NoopImport implements Import {}
